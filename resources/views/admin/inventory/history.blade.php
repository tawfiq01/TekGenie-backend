<x-layouts.admin title="Stock history">
    <h1 class="text-2xl font-bold text-ink">{{ $variant->sku }} — {{ $variant->product->name }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Current stock: <strong>{{ $variant->stock }}</strong></p>

    <a href="{{ route('admin.inventory.index') }}" class="mt-2 inline-block text-sm text-link hover:underline">&larr; Back to inventory</a>

    <form method="POST" action="{{ route('admin.inventory.adjust', $variant) }}" class="mt-6 max-w-md">
        @csrf
        <x-admin.form-section title="Adjust stock">
            <x-admin.input label="Quantity (positive to add, negative to remove)" name="quantity" type="number" :value="old('quantity')" required />
            <x-admin.input label="Reason" name="reason" :value="old('reason')" required />
        </x-admin.form-section>

        <button type="submit"
                class="mt-4 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save adjustment
        </button>
    </form>

    <div class="mt-10">
        <h2 class="text-lg font-semibold text-ink">Movement history</h2>

        <div class="mt-4">
            <x-admin.table :headers="['When', 'Type', 'Quantity', 'Balance after', 'Reason', 'Staff']">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-3 text-ink-soft">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-ink">{{ ucfirst(str_replace('_', ' ', $movement->type->value)) }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $movement->balance_after }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $movement->reason }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $movement->admin?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-ink-soft">No stock movements yet.</td>
                    </tr>
                @endforelse
            </x-admin.table>

            <div class="mt-6">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
