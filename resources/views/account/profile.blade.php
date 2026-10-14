<x-layouts.app title="Your profile">
    <div class="mx-auto max-w-xl space-y-8">
        <h1 class="text-2xl font-bold text-ink">Your profile</h1>

        @if (session('status'))
            <p class="rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
        @endif

        <div class="rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Profile information</h2>

            @if ($errors->updateProfileInformation->any())
                <div class="mt-3 rounded-[var(--radius-sm)] bg-danger-soft px-3 py-2 text-sm text-danger">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->updateProfileInformation->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-3 space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-ink">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-ink">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-ink">Mobile number</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>

                <button type="submit" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                    Save profile
                </button>
            </form>
        </div>

        <div class="rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Change password</h2>
            <p class="mt-1 text-xs text-ink-soft">Changing your password logs you out of any other device.</p>

            @if ($errors->updatePassword->any())
                <div class="mt-3 rounded-[var(--radius-sm)] bg-danger-soft px-3 py-2 text-sm text-danger">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->updatePassword->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user-password.update') }}" class="mt-3 space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-ink">Current password</label>
                    <input id="current_password" name="current_password" type="password" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-ink">New password</label>
                    <input id="password" name="password" type="password" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-ink">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>

                <button type="submit" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                    Update password
                </button>
            </form>
        </div>

        <div class="rounded-[var(--radius-md)] border border-danger p-4">
            <h2 class="font-semibold text-danger">Delete account</h2>
            <p class="mt-1 text-xs text-ink-soft">Your personal details are removed; past orders are kept for our records.</p>

            <form method="POST" action="{{ route('account.destroy') }}" class="mt-3 space-y-3" onsubmit="return confirm('Delete your account? This cannot be undone.');">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password" class="block text-sm font-medium text-ink">Confirm with your password</label>
                    <input id="delete_password" name="password" type="password" class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    @error('password')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded-[var(--radius-pill)] bg-danger px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    Delete my account
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
