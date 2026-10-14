<x-layouts.admin title="Staff">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Staff</h1>
        <a href="{{ route('admin.staff.create') }}"
           class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Add staff
        </a>
    </div>

    <div class="mt-6 overflow-hidden rounded-[var(--radius-md)] border border-border">
        <table class="w-full text-left text-sm">
            <thead class="bg-bg-subtle text-ink-soft">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Roles</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-bg">
                @foreach ($staffMembers as $staffMember)
                    <tr>
                        <td class="px-4 py-3 text-ink">{{ $staffMember->name }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $staffMember->email }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $staffMember->roles->pluck('name')->join(', ') }}</td>
                        <td class="px-4 py-3">
                            @if ($staffMember->is_active)
                                <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                            @else
                                <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.staff.edit', $staffMember) }}" class="text-link hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $staffMembers->links() }}
    </div>
</x-layouts.admin>
