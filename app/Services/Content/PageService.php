<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Services\Audit\AuditLogger;
use App\Support\RichTextSanitizer;

/** FR-STF-007: the page set is fixed (seeded) — staff edit content, not create/delete pages. */
class PageService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<string, mixed>  $data */
    public function update(Page $page, array $data): Page
    {
        $before = $page->only(['title', 'slug', 'is_published']);

        $page->forceFill([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'body' => RichTextSanitizer::sanitize($data['body']) ?? '',
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'show_in_footer' => $data['show_in_footer'] ?? false,
        ])->save();

        $this->audit->log('admin.page.updated', $page, [
            'before' => $before,
            'after' => $page->only(['title', 'slug', 'is_published']),
        ]);

        return $page;
    }
}
