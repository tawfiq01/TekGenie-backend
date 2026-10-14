<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    public function url(string $size = 'card'): string
    {
        return Storage::disk('public')->url("{$this->path}-{$size}.webp");
    }

    public function fallbackUrl(): string
    {
        return Storage::disk('public')->url("{$this->path}-fallback.jpg");
    }
}
