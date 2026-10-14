<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'product_id', 'option_value_id', 'path', 'alt_text', 'width', 'height', 'is_main', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<OptionValue, $this> */
    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class);
    }
}
