<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Reviews\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** FR-REV-001; BR-16; AC-REV-01 (ineligible submission refused with 403). */
class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Please verify your email address before writing a review.');
        }

        if (! $this->reviews->canReview($user, $product)) {
            abort(403, 'You can only review products from your delivered orders.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->reviews->submit($user, $product, (int) $data['rating'], $data['title'] ?? null, $data['body'] ?? null);

        return back()->with('status', 'Thanks! Your review will appear once it has been approved.');
    }
}
