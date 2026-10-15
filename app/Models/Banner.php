<?php

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    protected $fillable = [
        'placement', 'title', 'subtitle', 'button_label', 'link_url',
        'image_desktop_path', 'image_mobile_path', 'alt_text',
        'starts_at', 'ends_at', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('storefront.home'));
        static::deleted(fn () => Cache::forget('storefront.home'));
    }

    public function isCurrentlyScheduled(): bool
    {
        $now = now();

        return ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->ends_at === null || $this->ends_at->gte($now));
    }
}
