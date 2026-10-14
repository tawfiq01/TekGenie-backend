<x-layouts.admin title="Roles">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Roles</h1>
        <a href="{{ route('admin.roles.create') }}"
           class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Add role
        </a>
    </div>

    <div class="mt-6 space-y-4">
        @foreach ($roles as $role)
            <div class="rounded-[var(--radius-md)] border border-border bg-bg p-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-ink">{{ $role->name }}</h2>
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm text-link hover:underline">Edit</a>
                </div>
                <p class="mt-2 text-sm text-ink-soft">{{ $role->permissions->pluck('name')->join(', ') }}</p>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
