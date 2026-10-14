<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $base_charge
 * @property string|null $free_delivery_threshold
 */
class DeliveryZone extends Model
{
    protected $fillable = [
        'name', 'base_charge', 'free_delivery_threshold', 'estimate_text', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_charge' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<District, $this> */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /** @return HasMany<Area, $this> */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }
}
