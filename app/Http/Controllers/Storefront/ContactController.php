<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreContactMessageRequest;
use App\Models\Page;
use App\Models\Setting;
use App\Services\Content\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-STF-008. */
class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function show(): View
    {
        return view('storefront.contact', [
            'page' => Page::query()->where('slug', 'contact')->first(),
            'contactPhone' => Setting::get('store', 'contact_phone'),
            'contactEmail' => Setting::get('store', 'contact_email'),
            'contactAddress' => Setting::get('store', 'contact_address'),
            'businessHours' => Setting::get('store', 'business_hours'),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $this->contact->submit($request->validated(), $request->ip());

        return redirect()->route('contact.show')->with('status', "Thanks — we've received your message and will get back to you soon.");
    }
}
