<x-layouts.admin title="Edit page">
    <h1 class="text-2xl font-bold text-ink">Edit page — {{ $page->title }}</h1>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="mt-6 max-w-2xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.input label="Title" name="title" :value="old('title', $page->title)" required />
            <x-admin.input label="Slug" name="slug" :value="old('slug', $page->slug)" required />
            <x-admin.textarea label="Body (HTML)" name="body" :value="old('body', $page->body)" :rows="12" />
            <x-admin.input label="Meta title" name="meta_title" :value="old('meta_title', $page->meta_title)" />
            <x-admin.textarea label="Meta description" name="meta_description" :value="old('meta_description', $page->meta_description)" :rows="2" />
            <x-admin.checkbox label="Published" name="is_published" :checked="$page->is_published" />
            <x-admin.checkbox label="Show in footer" name="show_in_footer" :checked="$page->show_in_footer" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>
</x-layouts.admin>
