<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Models\Address;
use App\Models\Area;
use App\Models\Cart;
use App\Models\CheckoutToken;
use App\Models\District;
use App\Models\Division;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** FR-CHK-001..012. */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkout,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->current();

        if ($cart->items()->count() === 0) {
            return redirect()->route('cart.show')->with('status', 'Your cart is empty.');
        }

        $district = $request->filled('district_id') ? District::find($request->integer('district_id')) : null;
        $area = $request->filled('area_id') ? Area::find($request->integer('area_id')) : null;

        $summary = $this->checkout->buildSummary($cart, $district, $area, session('checkout.coupon_code'));

        return view('storefront.checkout', [
            'cart' => $cart,
            'summary' => $summary,
            'divisions' => Division::query()->orderBy('name')->get(),
            'districts' => $district ? District::where('division_id', $district->division_id)->orderBy('name')->get() : collect(),
            'selectedDistrict' => $district,
            'selectedArea' => $area,
            'addresses' => Auth::guard('web')->check() ? Address::where('user_id', Auth::guard('web')->id())->orderByDesc('is_default')->get() : collect(),
            'token' => $this->getOrCreateToken($cart),
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $this->assertNotRateLimited($request);

        $data = $request->validated();

        if (! empty($data['address_id'])) {
            $address = Address::where('id', $data['address_id'])->where('user_id', Auth::guard('web')->id())->firstOrFail();
            $data = array_merge($data, [
                'name' => $address->recipient_name,
                'phone' => $address->phone,
                'district_id' => $address->district_id,
                'area_id' => $address->area_id,
                'area_text' => $address->area_text,
                'address_line' => $address->address_line,
                'postcode' => $address->postcode,
            ]);
        }

        try {
            $order = $this->checkout->placeOrder(
                $this->cartService->current(),
                $data,
                $data['token'],
                Auth::guard('web')->user(),
                $request->ip(),
                $request->userAgent(),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        session()->forget('checkout.coupon_code');

        if (Auth::guard('web')->check()) {
            return redirect()->route('checkout.confirmation', $order);
        }

        // Guests have no session to prove ownership — a temporary signed URL
        // stands in for it (FR-CHK-010: "customer session or signed guest link").
        return redirect()->to(
            URL::temporarySignedRoute('checkout.confirmation', now()->addHours(24), ['order' => $order->id])
        );
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate(['coupon_code' => ['required', 'string', 'max:32']]);
        session(['checkout.coupon_code' => $request->input('coupon_code')]);

        return back();
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('checkout.coupon_code');

        return back();
    }

    private function getOrCreateToken(Cart $cart): CheckoutToken
    {
        $existing = CheckoutToken::where('cart_id', $cart->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return CheckoutToken::create([
            'token' => (string) Str::random(40),
            'cart_id' => $cart->id,
            'session_id' => session()->getId(),
            'expires_at' => now()->addHours(2),
        ]);
    }

    private function assertNotRateLimited(Request $request): void
    {
        $ipKey = 'checkout-ip:'.$request->ip();
        $phoneKey = 'checkout-phone:'.$request->input('phone');

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($phoneKey, 5)) {
            throw ValidationException::withMessages([
                'cart' => 'Too many checkout attempts — please try again later.',
            ])->status(429);
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($phoneKey, 3600);
    }
}
