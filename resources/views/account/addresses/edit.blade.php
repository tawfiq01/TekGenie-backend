<x-layouts.app title="Edit address">
    <div class="mx-auto max-w-xl">
        <h1 class="text-2xl font-bold text-ink">Edit address</h1>

        @if ($errors->any())
            <div class="mt-4 rounded-[var(--radius-sm)] bg-danger-soft px-4 py-3 text-sm text-danger">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.addresses.update', $address) }}" class="mt-6 space-y-4"
              x-data="{ divisionId: '{{ old('division_id', $address->division_id) }}', districts: @js($districts->map->only(['id', 'name', 'division_id'])) }">
            @csrf
            @method('PUT')

            <div>
                <label for="label" class="block text-sm font-medium text-ink">Label (optional, e.g. Home, Office)</label>
                <input id="label" name="label" type="text" value="{{ old('label', $address->label) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="recipient_name" class="block text-sm font-medium text-ink">Recipient name</label>
                <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $address->recipient_name) }}" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-ink">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $address->phone) }}" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="division_id" class="block text-sm font-medium text-ink">Division</label>
                    <select id="division_id" name="division_id" x-model="divisionId" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                        <option value="">— Choose —</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="district_id" class="block text-sm font-medium text-ink">District</label>
                    <select id="district_id" name="district_id" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                        <template x-for="district in districts.filter(d => String(d.division_id) === divisionId)" :key="district.id">
                            <option :value="district.id" x-text="district.name" :selected="district.id == {{ old('district_id', $address->district_id) }}"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div>
                <label for="area_text" class="block text-sm font-medium text-ink">Area / thana (optional)</label>
                <input id="area_text" name="area_text" type="text" value="{{ old('area_text', $address->area_text) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="address_line" class="block text-sm font-medium text-ink">Street address</label>
                <input id="address_line" name="address_line" type="text" value="{{ old('address_line', $address->address_line) }}" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="postcode" class="block text-sm font-medium text-ink">Postcode (optional)</label>
                <input id="postcode" name="postcode" type="text" value="{{ old('postcode', $address->postcode) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }} {{ $address->is_default ? 'disabled' : '' }}>
                Set as default address
            </label>

            <button type="submit" class="w-full rounded-[var(--radius-pill)] bg-ink px-6 py-3 text-sm font-semibold text-white hover:bg-ink-soft">
                Save changes
            </button>
        </form>
    </div>
</x-layouts.app>
