<x-layouts.admin title="Audit log">
    <h1 class="text-2xl font-bold text-ink">Audit log</h1>

    <form method="GET" action="{{ route('admin.audit-log.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="action" class="block text-xs font-medium text-ink-soft">Action</label>
            <input id="action" name="action" type="text" value="{{ request('action') }}"
                   class="mt-1 rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
        </div>
        <div>
            <label for="subject_type" class="block text-xs font-medium text-ink-soft">Subject type</label>
            <input id="subject_type" name="subject_type" type="text" value="{{ request('subject_type') }}"
                   class="mt-1 rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
        </div>
        <div>
            <label for="actor_id" class="block text-xs font-medium text-ink-soft">Actor ID</label>
            <input id="actor_id" name="actor_id" type="number" value="{{ request('actor_id') }}"
                   class="mt-1 w-24 rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
        </div>
        <div>
            <label for="date_from" class="block text-xs font-medium text-ink-soft">From</label>
            <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                   class="mt-1 rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
        </div>
        <div>
            <label for="date_to" class="block text-xs font-medium text-ink-soft">To</label>
            <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                   class="mt-1 rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
        </div>
        <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Filter
        </button>
    </form>

    <div class="mt-6 overflow-hidden rounded-[var(--radius-md)] border border-border">
        <table class="w-full text-left text-sm">
            <thead class="bg-bg-subtle text-ink-soft">
                <tr>
                    <th class="px-4 py-3">When</th>
                    <th class="px-4 py-3">Actor</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-bg">
                @forelse ($entries as $entry)
                    <tr>
                        <td class="px-4 py-3 text-ink-soft">{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-ink">{{ $entry->actor_type }}#{{ $entry->actor_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-ink">{{ $entry->action }}</td>
                        <td class="px-4 py-3 text-ink-soft">
                            {{ $entry->subject_type ? class_basename($entry->subject_type).'#'.$entry->subject_id : '—' }}
                        </td>
                        <td class="px-4 py-3 text-ink-soft">{{ $entry->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-ink-soft">No audit entries match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $entries->links() }}
    </div>
</x-layouts.admin>
