<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreAddressRequest;
use App\Http\Requests\Storefront\UpdateAddressRequest;
use App\Models\Address;
use App\Models\District;
use App\Models\Division;
use App\Services\Customers\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-ACC-006; BR-19 (a customer only ever sees their own addresses — 404, not 403, for anyone else's). */
class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses) {}

    public function index(): View
    {
        return view('account.addresses.index', [
            'addresses' => Auth::guard('web')->user()->addresses()->orderByDesc('is_default')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('account.addresses.create', [
            'divisions' => Division::query()->orderBy('name')->get(),
            'districts' => District::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->addresses->create(Auth::guard('web')->user(), $request->validated());

        return redirect()->route('account.addresses.index')->with('status', 'Address added.');
    }

    public function edit(Address $address): View
    {
        $this->authorizeOwner($address);

        return view('account.addresses.edit', [
            'address' => $address,
            'divisions' => Division::query()->orderBy('name')->get(),
            'districts' => District::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorizeOwner($address);

        $this->addresses->update($address, $request->validated());

        return redirect()->route('account.addresses.index')->with('status', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorizeOwner($address);

        $this->addresses->delete($address);

        return redirect()->route('account.addresses.index')->with('status', 'Address removed.');
    }

    public function setDefault(Address $address): RedirectResponse
    {
        $this->authorizeOwner($address);

        $this->addresses->setDefault($address);

        return redirect()->route('account.addresses.index')->with('status', 'Default address updated.');
    }

    private function authorizeOwner(Address $address): void
    {
        if ($address->user_id !== Auth::guard('web')->id()) {
            throw new NotFoundHttpException;
        }
    }
}
