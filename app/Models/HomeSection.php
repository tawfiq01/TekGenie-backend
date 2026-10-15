<?php

namespace App\Models;

use App\Enums\HomeSectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @property HomeSectionType $type
 */
class HomeSection extends Model
{
    protected $fillable = ['type', 'title', 'settings', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'type' => HomeSectionType::class,
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('storefront.home'));
        static::deleted(fn () => Cache::forget('storefront.home'));
    }

    /** @return HasMany<HomeSectionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order');
    }
}
