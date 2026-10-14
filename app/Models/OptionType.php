<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionType extends Model
{
    protected $fillable = ['name', 'display_style', 'sort_order'];

    /** @return HasMany<OptionValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_option_type');
    }
}
