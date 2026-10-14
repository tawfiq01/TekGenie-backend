<?php

namespace App\Models;

use App\Enums\HomeSectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return HasMany<HomeSectionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order');
    }
}
