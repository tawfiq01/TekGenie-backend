<x-layouts.app title="Checkout">
    <h1 class="text-2xl font-bold text-ink">Checkout</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-[var(--radius-sm)] bg-danger-soft px-4 py-3 text-sm text-danger">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-[1fr_380px]">
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token->token }}">
            @foreach ($summary->items as $item)
                <input type="hidden" name="expected_prices[{{ $item->product_variant_id }}]" value="{{ $item->variant->price }}">
            @endforeach

            @if ($addresses->isNotEmpty())
                <div class="rounded-[var(--radius-md)] border border-border p-4">
                    <h2 class="font-semibold text-ink">Delivery address</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($addresses as $address)
                            <label class="flex items-start gap-2 text-sm text-ink-soft">
                                <input type="radio" name="address_id" value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }} class="mt-1">
                                <span>{{ $address->recipient_name }}, {{ $address->phone }} — {{ $address->address_line }}, {{ $address->district->name ?? '' }}</span>
                            </label>
                        @endforeach
                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="radio" name="address_id" value="">
                            Use a different address
                        </label>
                    </div>
                </div>
            @endif

            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <h2 class="font-semibold text-ink">Contact & delivery details</h2>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="name" class="block text-sm font-medium text-ink">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-ink">Mobile number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-ink">Email (optional)</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="division_id" class="block text-sm font-medium text-ink">Division</label>
                            <select id="division_id" name="division_id" onchange="this.form.submit()" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                                <option value="">— Choose —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected($selectedDistrict?->division_id === $division->id)>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="district_id" class="block text-sm font-medium text-ink">District</label>
                            <select id="district_id" name="district_id" onchange="this.form.submit()" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                                <option value="">— Choose division first —</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}" @selected($selectedDistrict?->id === $district->id)>{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($selectedDistrict && $summary->zone === null)
                        <p class="rounded-[var(--radius-sm)] bg-warning-soft px-3 py-2 text-sm text-warning">
                            We don't deliver to this district yet — please contact us to arrange delivery.
                        </p>
                    @endif

                    <div>
                        <label for="area_text" class="block text-sm font-medium text-ink">Area / thana (optional)</label>
                        <input id="area_text" name="area_text" type="text" value="{{ old('area_text') }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label for="address_line" class="block text-sm font-medium text-ink">Street address</label>
                        <input id="address_line" name="address_line" type="text" value="{{ old('address_line') }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-ink">Postcode (optional)</label>
                            <input id="postcode" name="postcode" type="text" value="{{ old('postcode') }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="delivery_instructions" class="block text-sm font-medium text-ink">Delivery instructions (optional)</label>
                        <textarea id="delivery_instructions" name="delivery_instructions" rows="2" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">{{ old('delivery_instructions') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <h2 class="font-semibold text-ink">Payment method</h2>
                <label class="mt-2 flex items-center gap-2 text-sm text-ink-soft">
                    <input type="radio" name="payment_method" value="cod" checked>
                    Cash on Delivery
                </label>
            </div>

            <button type="submit" class="w-full rounded-[var(--radius-pill)] bg-ink px-6 py-3 text-sm font-semibold text-white hover:bg-ink-soft">
                Place order
            </button>
        </form>

        <aside class="h-fit rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Order summary</h2>

            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($summary->items as $item)
                    <li class="flex justify-between text-ink-soft">
                        <span>{{ $item->variant->product->name }} × {{ $item->quantity }}</span>
                        <span>৳{{ number_format($item->variant->price * $item->quantity, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4 border-t border-border pt-4">
                @if (session('checkout.coupon_code'))
                    <form method="POST" action="{{ route('checkout.coupon.remove') }}" class="mb-3 flex items-center justify-between text-sm">
                        @csrf
                        @method('DELETE')
                        <span class="text-ink-soft">Coupon: {{ session('checkout.coupon_code') }}</span>
                        <button type="submit" class="text-link hover:underline">Remove</button>
                    </form>
                    @if ($summary->couponError)
                        <p class="mb-3 text-sm text-danger">{{ $summary->couponError }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="mb-3 flex gap-2">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="Coupon code" class="flex-1 rounded-[var(--radius-sm)] border border-border px-2 py-1.5 text-sm">
                        <button type="submit" class="rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm text-ink hover:bg-bg-subtle">Apply</button>
                    </form>
                @endif

                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-soft">Subtotal</dt><dd class="text-ink">৳{{ $summary->pricing->subtotal() }}</dd></div>
                    @if ($summary->pricing->discountPoisha > 0)
                        <div class="flex justify-between"><dt class="text-ink-soft">Discount</dt><dd class="text-success">&minus;৳{{ $summary->pricing->discount() }}</dd></div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-ink-soft">Delivery{{ $summary->zone ? ' ('.$summary->zone->name.')' : '' }}</dt>
                        <dd class="text-ink">৳{{ $summary->pricing->deliveryCharge() }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-border pt-2 text-base font-semibold"><dt>Total</dt><dd>৳{{ $summary->pricing->grandTotal() }}</dd></div>
                </dl>

                @if ($summary->zone?->estimate_text)
                    <p class="mt-2 text-xs text-ink-soft">Estimated delivery: {{ $summary->zone->estimate_text }}</p>
                @endif
            </div>
        </aside>
    </div>
</x-layouts.app>
