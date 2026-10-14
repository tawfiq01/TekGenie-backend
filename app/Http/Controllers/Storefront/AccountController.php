<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Customers\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-ACC-005/011. */
class AccountController extends Controller
{
    public function __construct(private readonly AccountDeletionService $deletion) {}

    public function index(): View
    {
        $user = Auth::guard('web')->user();

        return view('account.dashboard', [
            'user' => $user,
            'recentOrders' => $user->orders()->latest('placed_at')->take(5)->get(),
        ]);
    }

    public function profile(): View
    {
        return view('account.profile', ['user' => Auth::guard('web')->user()]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        $user = Auth::guard('web')->user();
        Auth::guard('web')->logout();
        $this->deletion->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account has been deleted.');
    }
}
