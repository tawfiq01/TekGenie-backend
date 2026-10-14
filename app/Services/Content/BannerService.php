<?php

namespace App\Services\Content;

use App\Models\Banner;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** FR-STF-003. */
class BannerService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<string, mixed>  $data */
    public function create(array $data, UploadedFile $desktopImage, ?UploadedFile $mobileImage): Banner
    {
        $banner = Banner::create([
            'placement' => $data['placement'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'button_label' => $data['button_label'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'image_desktop_path' => Storage::disk('public')->putFile('banners', $desktopImage),
            'image_mobile_path' => $mobileImage ? Storage::disk('public')->putFile('banners', $mobileImage) : null,
            'alt_text' => $data['alt_text'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        $this->audit->log('admin.banner.created', $banner, ['placement' => $banner->placement]);

        return $banner;
    }

    /** @param  array<string, mixed>  $data */
    public function update(Banner $banner, array $data, ?UploadedFile $desktopImage, ?UploadedFile $mobileImage): Banner
    {
        $before = $banner->only(['placement', 'title', 'is_active']);

        $banner->forceFill([
            'placement' => $data['placement'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'button_label' => $data['button_label'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'alt_text' => $data['alt_text'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'sort_order' => $data['sort_order'] ?? $banner->sort_order,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $banner->is_active,
        ]);

        if ($desktopImage) {
            $banner->image_desktop_path = Storage::disk('public')->putFile('banners', $desktopImage);
        }
        if ($mobileImage) {
            $banner->image_mobile_path = Storage::disk('public')->putFile('banners', $mobileImage);
        }

        $banner->save();

        $this->audit->log('admin.banner.updated', $banner, [
            'before' => $before,
            'after' => $banner->only(['placement', 'title', 'is_active']),
        ]);

        return $banner;
    }

    public function destroy(Banner $banner): void
    {
        Storage::disk('public')->delete(array_filter([$banner->image_desktop_path, $banner->image_mobile_path]));
        $banner->delete();

        $this->audit->log('admin.banner.deleted', null, ['banner_id' => $banner->id]);
    }
}
