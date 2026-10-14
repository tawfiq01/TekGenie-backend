<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\Checkout\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-PRM-001..004, gated by coupons.manage. */
class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()->withCount('usages')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->coupons->create($request->validated(), $actor);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', [
            'coupon' => $coupon->load(['categories', 'products']),
            'categories' => Category::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
            'totalDiscount' => $coupon->usages()->whereNull('released_at')->sum('discount_amount'),
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->coupons->update($coupon, $request->validated());

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon updated.');
    }
}
