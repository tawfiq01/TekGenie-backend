<x-layouts.admin title="Edit staff">
    <h1 class="text-2xl font-bold text-ink">Edit staff — {{ $staffMember->name }}</h1>

    @php($currentRoles = old('roles', $staffMember->roles->pluck('name')->all()))

    <form method="POST" action="{{ route('admin.staff.update', $staffMember) }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-ink">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $staffMember->name) }}" required
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('name')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ink">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $staffMember->email) }}" required
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-soft">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staffMember->is_active) ? 'checked' : '' }}
                   class="rounded border-border">
            Active
        </label>
        @error('is_active')
            <p class="text-sm text-danger">{{ $message }}</p>
        @enderror

        <fieldset>
            <legend class="text-sm font-medium text-ink">Roles</legend>
            <div class="mt-2 space-y-2">
                @foreach ($roles as $role)
                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                               {{ in_array($role->name, $currentRoles) ? 'checked' : '' }}
                               class="rounded border-border">
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
            @error('roles')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </fieldset>

        <button type="submit"
                class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>
</x-layouts.admin>
