<?php

namespace App\Services\Checkout;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * BR-12: one coupon per order; must be active, within its dates, under its
 * usage limits and meet its minimum order value. FR-PRM-003: usage is
 * counted atomically inside the order transaction so the last slot of a
 * limited coupon can never be double-spent by concurrent orders.
 */
class CouponService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function findValid(string $code, int $subtotalPoisha, ?User $user, string $phone): Coupon
    {
        $coupon = Coupon::query()->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'This coupon code was not found.']);
        }

        if (! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is no longer active.']);
        }

        $now = now();
        if ($coupon->starts_at !== null && $now->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is not active yet.']);
        }
        if ($coupon->ends_at !== null && $now->gt($coupon->ends_at)) {
            throw ValidationException::withMessages(['coupon' => 'This coupon has expired.']);
        }

        if ($coupon->min_subtotal !== null && $subtotalPoisha < (int) round(((float) $coupon->min_subtotal) * 100)) {
            throw ValidationException::withMessages(['coupon' => "This coupon needs a minimum order of ৳{$coupon->min_subtotal}."]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => 'This coupon has reached its usage limit.']);
        }

        if ($coupon->per_customer_limit !== null) {
            $priorUses = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->whereNull('released_at')
                ->where(fn ($q) => $q
                    ->when($user, fn ($q2) => $q2->orWhere('user_id', $user->id))
                    ->orWhere('phone', $phone))
                ->count();

            if ($priorUses >= $coupon->per_customer_limit) {
                throw ValidationException::withMessages(['coupon' => 'You have already used this coupon.']);
            }
        }

        return $coupon;
    }

    public function isLineEligible(Coupon $coupon, Product $product): bool
    {
        return match ($coupon->applies_to) {
            'categories' => $coupon->categories()->whereIn('categories.id', $product->categories()->pluck('categories.id'))->exists()
                || $coupon->categories()->where('categories.id', $product->category_id)->exists(),
            'products' => $coupon->products()->where('products.id', $product->id)->exists(),
            default => true,
        };
    }

    /**
     * Atomically claims one usage slot and records it. Must be called inside
     * the same DB transaction as order creation — if this throws, the whole
     * order must roll back (FR-PRM-003).
     */
    public function recordUsage(Coupon $coupon, Order $order, ?User $user, string $phone, string $discountAmount): CouponUsage
    {
        $query = DB::table('coupons')->where('id', $coupon->id);

        if ($coupon->usage_limit !== null) {
            $query->whereColumn('used_count', '<', 'usage_limit');
        }

        $affected = $query->update(['used_count' => DB::raw('used_count + 1')]);

        if ($affected === 0) {
            throw ValidationException::withMessages(['coupon' => 'This coupon just reached its usage limit — please remove it to continue.']);
        }

        return CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'phone' => $phone,
            'discount_amount' => $discountAmount,
        ]);
    }

    /** Releases a coupon slot on cancellation (wired in when Orders/§9 lands). */
    public function release(Order $order): void
    {
        $usage = CouponUsage::query()->where('order_id', $order->id)->whereNull('released_at')->first();

        if (! $usage) {
            return;
        }

        DB::transaction(function () use ($usage) {
            $usage->forceFill(['released_at' => now()])->save();
            DB::table('coupons')->where('id', $usage->coupon_id)->update(['used_count' => DB::raw('GREATEST(used_count - 1, 0)')]);
        });

        $this->audit->log('admin.coupon.usage-released', $usage->coupon, ['order_id' => $order->id]);
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data, Admin $actor): Coupon
    {
        $coupon = Coupon::create([
            'code' => mb_strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'max_discount' => $data['max_discount'] ?? null,
            'min_subtotal' => $data['min_subtotal'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_customer_limit' => $data['per_customer_limit'] ?? null,
            'applies_to' => $data['applies_to'] ?? 'all',
            'is_active' => true,
            'created_by' => $actor->id,
        ]);

        if (($data['applies_to'] ?? 'all') === 'categories') {
            $coupon->categories()->sync($data['category_ids'] ?? []);
        } elseif (($data['applies_to'] ?? 'all') === 'products') {
            $coupon->products()->sync($data['product_ids'] ?? []);
        }

        $this->audit->log('admin.coupon.created', $coupon, ['code' => $coupon->code]);

        return $coupon;
    }

    /** @param  array<string, mixed>  $data */
    public function update(Coupon $coupon, array $data): Coupon
    {
        $before = $coupon->only(['code', 'value', 'is_active']);

        $coupon->forceFill([
            'code' => mb_strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'max_discount' => $data['max_discount'] ?? null,
            'min_subtotal' => $data['min_subtotal'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_customer_limit' => $data['per_customer_limit'] ?? null,
            'applies_to' => $data['applies_to'] ?? 'all',
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $coupon->is_active,
        ])->save();

        if (($data['applies_to'] ?? 'all') === 'categories') {
            $coupon->categories()->sync($data['category_ids'] ?? []);
            $coupon->products()->sync([]);
        } elseif (($data['applies_to'] ?? 'all') === 'products') {
            $coupon->products()->sync($data['product_ids'] ?? []);
            $coupon->categories()->sync([]);
        } else {
            $coupon->categories()->sync([]);
            $coupon->products()->sync([]);
        }

        $this->audit->log('admin.coupon.updated', $coupon, [
            'before' => $before,
            'after' => $coupon->only(['code', 'value', 'is_active']),
        ]);

        return $coupon;
    }
}
