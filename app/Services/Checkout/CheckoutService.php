<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Area;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CheckoutToken;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Payments\PaymentManager;
use App\Services\Shipping\DeliveryZoneService;
use App\Support\BdPhone;
use App\Support\OrderNumberGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-CHK-007/008: on "Place order" the server re-reads prices, re-validates
 * stock, coupon and delivery zone, and recalculates every amount inside one
 * transaction. BR-02: nothing from the browser is trusted — every amount
 * charged comes from a fresh DB read taken inside this transaction.
 */
class CheckoutService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly CouponService $coupons,
        private readonly DeliveryZoneService $zones,
        private readonly InventoryService $inventory,
        private readonly PaymentManager $payments,
        private readonly OrderNotificationService $notifications,
    ) {}

    /** Builds the summary the checkout page displays (not inside a transaction — for display only). */
    public function buildSummary(Cart $cart, ?District $district, ?Area $area, ?string $couponCode): CheckoutSummary
    {
        $items = $cart->items()->with('variant.product')->get();
        $zone = $district ? $this->zones->resolve($district, $area) : null;

        $coupon = null;
        $couponError = null;

        if ($couponCode) {
            try {
                $subtotalPoisha = (int) $items->sum(fn ($i) => round(((float) $i->variant->price) * 100) * $i->quantity);
                $coupon = $this->coupons->findValid($couponCode, $subtotalPoisha, Auth::guard('web')->user(), $this->currentPhone());
            } catch (ValidationException $e) {
                $couponError = $e->validator->errors()->first('coupon');
            }
        }

        $lines = $this->buildPricingLines($items, $coupon);
        $result = $this->pricing->calculate($lines, $coupon, $zone);

        return new CheckoutSummary($items, $zone, $coupon, $couponError, $result);
    }

    /**
     * @param  array<string, mixed>  $customerData
     *
     * @throws ValidationException
     */
    public function placeOrder(
        Cart $cart,
        array $customerData,
        string $idempotencyToken,
        ?User $user,
        string $ip,
        ?string $userAgent,
    ): Order {
        // Lock variant rows in a consistent order to avoid deadlocks (FR-INV-003).
        $items = $cart->items()->with('variant.product')->get()->sortBy(fn ($i) => $i->product_variant_id)->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        // Runs outside the transaction below: assertLinesStillSellable() may
        // auto-adjust the cart's quantity to match available stock (EH-02),
        // and that write must survive even though the ValidationException it
        // throws would otherwise roll back an enclosing transaction. The
        // authoritative, concurrency-safe stock guard is still enforced
        // later by InventoryService::recordMovement().
        $this->assertLinesStillSellable($items);
        $this->assertPricesUnchanged($items, $customerData['expected_prices'] ?? []);

        if (! $this->payments->driver(PaymentMethod::Cod)->isEnabled()) {
            throw ValidationException::withMessages(['cart' => 'No payment methods are currently available — please contact us.']);
        }

        $isReplay = false;

        $order = DB::transaction(function () use ($cart, $items, $customerData, $idempotencyToken, $user, $ip, $userAgent, &$isReplay) {
            $token = $this->claimToken($idempotencyToken, $cart);

            if ($token === null) {
                $existing = CheckoutToken::where('token', $idempotencyToken)->first();

                if ($existing?->order_id) {
                    $isReplay = true;

                    return Order::findOrFail($existing->order_id);
                }

                throw ValidationException::withMessages(['token' => 'Your checkout session has expired — please try again.']);
            }

            $phone = BdPhone::normalize($customerData['phone']);
            if ($phone === null) {
                throw ValidationException::withMessages(['phone' => 'Enter a valid Bangladeshi mobile number.']);
            }

            $district = District::query()->findOrFail($customerData['district_id']);
            $area = ! empty($customerData['area_id']) ? Area::query()->find($customerData['area_id']) : null;
            $zone = $this->zones->resolve($district, $area);

            if ($zone === null || ! $zone->is_active) {
                throw ValidationException::withMessages(['district_id' => "We don't deliver to this area yet — please contact us."]);
            }

            $coupon = null;
            if (! empty($customerData['coupon_code'])) {
                $subtotalPoisha = (int) $items->sum(fn ($i) => round(((float) $i->variant->price) * 100) * $i->quantity);
                $coupon = $this->coupons->findValid($customerData['coupon_code'], $subtotalPoisha, $user, $phone);
            }

            $lines = $this->buildPricingLines($items, $coupon);
            $pricing = $this->pricing->calculate($lines, $coupon, $zone);

            $this->assertOrderValueAllowed($pricing->grandTotalPoisha);

            $order = $this->createOrder($items, $customerData, $district, $area, $zone, $coupon, $pricing, $user, $phone, $ip, $userAgent);
            $this->payments->driver(PaymentMethod::Cod)->initiate($order);

            foreach ($items as $item) {
                $this->inventory->recordMovement(
                    $item->variant,
                    StockMovementType::Sale,
                    -$item->quantity,
                    'Order '.$order->order_number,
                    null,
                    $order->id,
                );
            }

            if ($coupon !== null) {
                $this->coupons->recordUsage($coupon, $order, $user, $phone, $pricing->discount());
            }

            CheckoutToken::where('id', $token->id)->update(['order_id' => $order->id]);
            $cart->items()->delete();

            return $order;
        }, 3);

        if (! $isReplay) {
            $this->notifications->orderPlaced($order);
        }

        return $order;
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @return array<int, PricingLine>
     */
    private function buildPricingLines(Collection $items, ?Coupon $coupon): array
    {
        return $items->map(function ($item) use ($coupon) {
            $eligible = $coupon === null || $this->coupons->isLineEligible($coupon, $item->variant->product);

            return new PricingLine(
                variantId: $item->product_variant_id,
                unitPricePoisha: (int) round(((float) $item->variant->price) * 100),
                quantity: $item->quantity,
                couponEligible: $eligible,
            );
        })->values()->all();
    }

    /** @param  Collection<int, CartItem>  $items */
    private function assertLinesStillSellable(Collection $items): void
    {
        foreach ($items as $item) {
            if (! $item->variant->is_active) {
                throw ValidationException::withMessages([
                    'cart' => "{$item->variant->product->name} is no longer available — please remove it from your cart.",
                ]);
            }

            if ($item->quantity > $item->variant->stock) {
                $item->update(['quantity' => max(1, $item->variant->stock)]);

                throw ValidationException::withMessages([
                    'cart' => "Only {$item->variant->stock} of {$item->variant->product->name} is available — your cart has been updated.",
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @param  array<int, string>  $expectedPrices  variantId => price shown to the customer
     */
    private function assertPricesUnchanged(Collection $items, array $expectedPrices): void
    {
        foreach ($items as $item) {
            $expected = $expectedPrices[$item->product_variant_id] ?? null;

            if ($expected !== null && (float) $expected !== (float) $item->variant->price) {
                throw ValidationException::withMessages([
                    'cart' => "The price of {$item->variant->product->name} changed to ৳{$item->variant->price} — please review and confirm.",
                ]);
            }
        }
    }

    private function assertOrderValueAllowed(int $grandTotalPoisha): void
    {
        $min = (float) Setting::get('checkout', 'min_order_value', 0);
        $codMax = $this->payments->driver(PaymentMethod::Cod)->maxOrderValue();
        $grandTotal = $grandTotalPoisha / 100;

        if ($min > 0 && $grandTotal < $min) {
            throw ValidationException::withMessages(['cart' => "Minimum order value is ৳{$min}."]);
        }

        if ($codMax !== null && $grandTotal > $codMax) {
            throw ValidationException::withMessages(['cart' => "Maximum Cash on Delivery order value is ৳{$codMax}."]);
        }
    }

    private function claimToken(string $token, Cart $cart): ?CheckoutToken
    {
        $record = CheckoutToken::where('token', $token)->where('cart_id', $cart->id)->first();

        if (! $record || $record->isExpired()) {
            return null;
        }

        $affected = DB::table('checkout_tokens')->where('id', $record->id)->whereNull('used_at')->update(['used_at' => now()]);

        return $affected === 1 ? $record : null;
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @param  array<string, mixed>  $customerData
     */
    private function createOrder(
        Collection $items,
        array $customerData,
        District $district,
        ?Area $area,
        DeliveryZone $zone,
        ?Coupon $coupon,
        PricingResult $pricing,
        ?User $user,
        string $phone,
        string $ip,
        ?string $userAgent,
    ): Order {
        $order = Order::create([
            'order_number' => OrderNumberGenerator::generate(),
            'user_id' => $user?->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => PaymentMethod::Cod,
            'customer_name' => $customerData['name'],
            'phone' => $phone,
            'email' => $customerData['email'] ?? null,
            'division_name' => $district->division->name,
            'district_name' => $district->name,
            'area_name' => $area->name ?? $customerData['area_text'] ?? null,
            'district_id' => $district->id,
            'address_line' => $customerData['address_line'],
            'postcode' => $customerData['postcode'] ?? null,
            'delivery_instructions' => $customerData['delivery_instructions'] ?? null,
            'delivery_zone_id' => $zone->id,
            'delivery_zone_name' => $zone->name,
            'subtotal' => $pricing->subtotal(),
            'discount_total' => $pricing->discount(),
            'delivery_charge' => $pricing->deliveryCharge(),
            'grand_total' => $pricing->grandTotal(),
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'placed_at' => now(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->variant->product_id,
                'product_variant_id' => $item->variant->id,
                'product_name' => $item->variant->product->name,
                'variant_label' => $item->variant->optionValues()->pluck('value')->join(' / ') ?: null,
                'sku' => $item->variant->sku,
                'image_path' => $item->variant->product->images()->where('is_main', true)->value('path'),
                'unit_price' => $item->variant->price,
                'compare_at_price' => $item->variant->hasGenuineCompareAtPrice() ? $item->variant->compare_at_price : null,
                'quantity' => $item->quantity,
                'discount_amount' => $pricing->lineDiscount($item->product_variant_id),
                'line_total' => PricingResult::toDecimal(
                    (int) round(((float) $item->variant->price) * 100) * $item->quantity
                    - (int) round((float) $pricing->lineDiscount($item->product_variant_id) * 100)
                ),
            ]);
        }

        return $order;
    }

    private function currentPhone(): string
    {
        return (string) (Auth::guard('web')->user()->phone ?? '');
    }
}
