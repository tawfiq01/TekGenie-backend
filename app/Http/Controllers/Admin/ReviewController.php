<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Review;
use App\Services\Reviews\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-REV-002/004, gated by reviews.moderate. */
class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $status = $request->filled('status') ? ReviewStatus::from($request->string('status')->toString()) : ReviewStatus::Pending;

        return view('admin.reviews.index', [
            'reviews' => Review::query()
                ->with(['product', 'user', 'orderItem'])
                ->where('status', $status)
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->reviews->moderate($actor, $review, ReviewStatus::Approved);

        return back()->with('status', 'Review approved.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:255']]);

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->reviews->moderate($actor, $review, ReviewStatus::Rejected, $data['note'] ?? null);

        return back()->with('status', 'Review rejected.');
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['reply' => ['required', 'string', 'max:2000']]);

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->reviews->reply($actor, $review, $data['reply']);

        return back()->with('status', 'Reply published.');
    }
}
