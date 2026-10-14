<?php

namespace App\Services\Content;

use App\Models\HomeSection;
use App\Services\Audit\AuditLogger;

/** FR-STF-001: sections are a fixed set — only title, order and active state are editable. */
class HomeSectionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<int, array{id: int, title: string, sort_order: int, is_active?: bool}>  $sections */
    public function updateAll(array $sections): void
    {
        foreach ($sections as $data) {
            $section = HomeSection::query()->find($data['id']);

            if (! $section) {
                continue;
            }

            $section->update([
                'title' => $data['title'],
                'sort_order' => $data['sort_order'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
        }

        $this->audit->log('admin.home-sections.updated', null, ['count' => count($sections)]);
    }
}
