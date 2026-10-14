<x-layouts.admin title="Edit role">
    <h1 class="text-2xl font-bold text-ink">Edit role — {{ $role->name }}</h1>

    @php($currentPermissions = old('permissions', $role->permissions->pluck('name')->all()))

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-ink">Role name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $role->name) }}" required
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('name')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <fieldset>
            <legend class="text-sm font-medium text-ink">Permissions</legend>
            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach ($permissions as $permission)
                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               {{ in_array($permission->name, $currentPermissions) ? 'checked' : '' }}
                               class="rounded border-border">
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>
            @error('permissions')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </fieldset>

        <button type="submit"
                class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>
</x-layouts.admin>
