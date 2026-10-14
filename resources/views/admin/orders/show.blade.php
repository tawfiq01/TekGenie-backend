<x-layouts.admin :title="'Order '.$order->order_number">
    @php($adminUser = auth('admin')->user())

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">{{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.packing-slip', $order) }}" target="_blank" class="text-sm text-link hover:underline">Print packing slip</a>
    </div>
    <p class="mt-1 text-sm text-ink-soft">
        Placed {{ $order->placed_at->format('Y-m-d H:i') }} ·
        <span class="font-medium text-ink">{{ $order->status->label() }}</span> ·
        Payment: <span class="font-medium text-ink">{{ $order->payment_status->label() }}</span>
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.form-section title="Items">
                <x-admin.table :headers="['Item', 'SKU', 'Qty', 'Unit price', 'Discount', 'Line total']">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-ink">{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }}
                                @if ($item->returned_quantity > 0)
                                    <span class="ml-1 text-xs text-warning">({{ $item->returned_quantity }} returned)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $item->sku }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-ink-soft">৳{{ $item->unit_price }}</td>
                            <td class="px-4 py-3 text-ink-soft">৳{{ $item->discount_amount }}</td>
                            <td class="px-4 py-3 text-ink-soft">৳{{ $item->line_total }}</td>
                        </tr>
                    @endforeach
                </x-admin.table>

                <dl class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-soft">Subtotal</dt><dd class="text-ink">৳{{ $order->subtotal }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-soft">Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</dt><dd class="text-ink">−৳{{ $order->discount_total }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-soft">Delivery ({{ $order->delivery_zone_name }})</dt><dd class="text-ink">৳{{ $order->delivery_charge }}</dd></div>
                    <div class="flex justify-between font-semibold"><dt class="text-ink">Grand total</dt><dd class="text-ink">৳{{ $order->grand_total }}</dd></div>
                </dl>
            </x-admin.form-section>

            <x-admin.form-section title="Delivery address">
                <p class="text-sm text-ink">{{ $order->customer_name }} · {{ $order->phone }}{{ $order->email ? ' · '.$order->email : '' }}</p>
                <p class="text-sm text-ink-soft">{{ $order->address_line }}</p>
                <p class="text-sm text-ink-soft">{{ $order->area_name ? $order->area_name.', ' : '' }}{{ $order->district_name }}, {{ $order->division_name }}{{ $order->postcode ? ' — '.$order->postcode : '' }}</p>
                @if ($order->delivery_instructions)
                    <p class="text-sm text-ink-soft">Instructions: {{ $order->delivery_instructions }}</p>
                @endif

                @can('orders.update-status')
                    @if (in_array($order->status, [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Confirmed], true))
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm text-link">Edit contact / address</summary>
                            <form method="POST" action="{{ route('admin.orders.address.update', $order) }}" class="mt-3 max-w-md space-y-3">
                                @csrf
                                @method('PUT')
                                <x-admin.input label="Name" name="customer_name" :value="old('customer_name', $order->customer_name)" required />
                                <x-admin.input label="Phone" name="phone" :value="old('phone', $order->phone)" required />
                                <x-admin.input label="Address line" name="address_line" :value="old('address_line', $order->address_line)" required />
                                <x-admin.input label="Postcode" name="postcode" :value="old('postcode', $order->postcode)" />
                                <x-admin.textarea label="Delivery instructions" name="delivery_instructions" :value="old('delivery_instructions', $order->delivery_instructions)" />
                                <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">Save</button>
                            </form>
                        </details>
                    @endif
                @endcan
            </x-admin.form-section>

            <x-admin.form-section title="Courier">
                <p class="text-sm text-ink-soft">{{ $order->courier_name ?: 'No courier set yet.' }}{{ $order->tracking_reference ? ' — '.$order->tracking_reference : '' }}</p>

                @can('orders.update-status')
                    <form method="POST" action="{{ route('admin.orders.courier.update', $order) }}" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PUT')
                        <x-admin.input label="Courier name" name="courier_name" :value="old('courier_name', $order->courier_name)" />
                        <x-admin.input label="Tracking reference" name="tracking_reference" :value="old('tracking_reference', $order->tracking_reference)" />
                        <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">Save</button>
                    </form>
                @endcan
            </x-admin.form-section>

            <x-admin.form-section title="Status history">
                <ul class="space-y-2 text-sm">
                    @forelse ($order->statusHistories as $history)
                        <li class="text-ink-soft">
                            <span class="text-ink">{{ $history->from_status ? ucfirst($history->from_status).' → ' : '' }}{{ ucfirst($history->to_status) }}</span>
                            — {{ $history->created_at->format('Y-m-d H:i') }} by {{ $history->actor_type }}
                            @if ($history->note) — "{{ $history->note }}" @endif
                            @unless ($history->is_customer_visible) <span class="text-xs text-muted">(staff only)</span> @endunless
                        </li>
                    @empty
                        <li class="text-ink-soft">No status changes yet.</li>
                    @endforelse
                </ul>
            </x-admin.form-section>

            <x-admin.form-section title="Internal notes">
                <ul class="space-y-2 text-sm">
                    @forelse ($order->notes as $note)
                        <li class="text-ink-soft"><span class="text-ink">{{ $note->admin?->name ?? 'Staff' }}</span> ({{ $note->created_at->format('Y-m-d H:i') }}): {{ $note->body }}</li>
                    @empty
                        <li class="text-ink-soft">No notes yet.</li>
                    @endforelse
                </ul>

                @can('orders.update-status')
                    <form method="POST" action="{{ route('admin.orders.notes.store', $order) }}" class="mt-3 max-w-lg">
                        @csrf
                        <x-admin.textarea label="Add a note (staff only, never shown to the customer)" name="body" :value="old('body')" />
                        <button type="submit" class="mt-2 rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">Add note</button>
                    </form>
                @endcan
            </x-admin.form-section>

            @can('orders.returns')
                @if (in_array($order->status, [\App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Delivered], true))
                    <x-admin.form-section title="Process a return">
                        <form method="POST" action="{{ route('admin.orders.returns.store', $order) }}">
                            @csrf
                            <x-admin.select label="Return type" name="type"
                                :options="collect(\App\Enums\ReturnType::cases())->filter(fn ($t) => $t->requiredOrderStatus() === $order->status)->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                                :selected="old('type')" placeholder="— Choose —" />
                            <x-admin.input label="Reason" name="reason" :value="old('reason')" required />

                            <div class="mt-4 space-y-3">
                                @foreach ($order->items as $item)
                                    @php($remaining = $item->quantity - $item->returned_quantity)
                                    @if ($remaining > 0)
                                        <div class="flex flex-wrap items-end gap-3 rounded-[var(--radius-sm)] border border-border p-3">
                                            <div class="text-sm text-ink">{{ $item->product_name }} ({{ $remaining }} available)</div>
                                            <x-admin.input label="Quantity" name="lines[{{ $item->id }}][quantity]" type="number" min="0" :max="$remaining" :value="old('lines.'.$item->id.'.quantity', 0)" />
                                            <x-admin.select label="Condition" name="lines[{{ $item->id }}][condition]"
                                                :options="collect(\App\Enums\ReturnCondition::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])"
                                                :selected="old('lines.'.$item->id.'.condition')" placeholder="—" />
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <button type="submit" class="mt-4 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">Process return</button>
                        </form>
                    </x-admin.form-section>
                @endif
            @endcan
        </div>

        <div class="space-y-6">
            @can('orders.update-status')
                @if (! empty($allowedTransitions))
                    <x-admin.form-section title="Update status">
                        <form method="POST" action="{{ route('admin.orders.status.update', $order) }}">
                            @csrf
                            <x-admin.select label="New status" name="status"
                                :options="collect($allowedTransitions)->mapWithKeys(fn ($s) => [$s->value => $s->label()])"
                                :selected="old('status')" placeholder="— Choose —" />
                            <x-admin.input label="Note (optional)" name="note" :value="old('note')" />
                            <button type="submit" class="mt-2 w-full rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">Save status</button>
                        </form>
                    </x-admin.form-section>
                @endif
            @endcan

            @can('orders.cancel')
                @if ($order->status->canTransitionTo(\App\Enums\OrderStatus::Cancelled))
                    <x-admin.form-section title="Cancel order">
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order? Stock and any coupon use will be released.');">
                            @csrf
                            <x-admin.input label="Reason" name="reason" :value="old('reason')" required />
                            <button type="submit" class="mt-2 w-full rounded-[var(--radius-pill)] bg-danger px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Cancel order</button>
                        </form>
                    </x-admin.form-section>
                @endif
            @endcan

            <x-admin.form-section title="Payment">
                @foreach ($order->payments as $payment)
                    <p class="text-sm text-ink">{{ $payment->method->label() }} — {{ $payment->status->label() }} (৳{{ $payment->amount }})</p>
                    @if ($payment->collected_amount !== null)
                        <p class="text-xs text-ink-soft">Collected ৳{{ $payment->collected_amount }} on {{ $payment->collected_at?->format('Y-m-d H:i') }}
                            by {{ $payment->recordedBy?->name ?? '—' }}</p>
                        @if ((float) $payment->collected_amount !== (float) $payment->amount)
                            <p class="text-xs text-warning">Variance: {{ $payment->provider_payload['variance_note'] ?? 'no reason recorded' }}</p>
                        @endif
                    @endif

                    @foreach ($payment->refunds as $refund)
                        <p class="text-xs text-ink-soft">Refunded ৳{{ $refund->amount }} ({{ $refund->method->label() }}) on {{ $refund->refunded_at->format('Y-m-d H:i') }} by {{ $refund->recordedBy->name }}</p>
                    @endforeach
                @endforeach

                @can('orders.payments')
                    @if (in_array($order->status, [\App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Delivered], true) && $order->payment_status === \App\Enums\PaymentStatus::Unpaid)
                        <form method="POST" action="{{ route('admin.orders.payments.collect', $order) }}" class="mt-3 space-y-3">
                            @csrf
                            <x-admin.input label="Amount collected" name="collected_amount" type="number" step="0.01" :value="old('collected_amount', $order->grand_total)" required />
                            <x-admin.input label="Variance reason (if different from order total)" name="variance_note" :value="old('variance_note')" />
                            <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">Record collection</button>
                        </form>
                    @endif

                    @if (in_array($order->payment_status, [\App\Enums\PaymentStatus::Paid, \App\Enums\PaymentStatus::PartiallyRefunded], true))
                        <form method="POST" action="{{ route('admin.orders.payments.refund', $order) }}" class="mt-3 space-y-3">
                            @csrf
                            <x-admin.input label="Refund amount" name="amount" type="number" step="0.01" :value="old('amount')" required />
                            <x-admin.select label="Method" name="method"
                                :options="collect(\App\Enums\RefundMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()])"
                                :selected="old('method')" placeholder="— Choose —" />
                            <x-admin.input label="Reference (optional)" name="reference" :value="old('reference')" />
                            <x-admin.input label="Note (optional)" name="note" :value="old('note')" />
                            <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">Record refund</button>
                        </form>
                    @endif
                @endcan
            </x-admin.form-section>

            @if ($order->returns->isNotEmpty())
                <x-admin.form-section title="Returns">
                    @foreach ($order->returns as $return)
                        <div class="border-b border-border pb-2 text-sm last:border-0">
                            <p class="text-ink">{{ $return->type->label() }} — {{ $return->reason }}</p>
                            <p class="text-ink-soft">{{ $return->processed_at->format('Y-m-d H:i') }} by {{ $return->processedBy->name }}</p>
                            @foreach ($return->items as $returnItem)
                                <p class="text-xs text-ink-soft">{{ $returnItem->orderItem->product_name }} × {{ $returnItem->quantity }} ({{ $returnItem->condition->label() }})</p>
                            @endforeach
                        </div>
                    @endforeach
                </x-admin.form-section>
            @endif
        </div>
    </div>
</x-layouts.admin>
