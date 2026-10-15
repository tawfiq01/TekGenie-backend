<?php

namespace App\Models;

use App\Exceptions\SlugMovedException;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image_path',
        'meta_title', 'meta_description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /** @return HasMany<Product, $this> */
    public function primaryProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /** @return BelongsToMany<Coupon, $this> */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_category');
    }

    /** FR-SET-005: an unresolved slug that matches a recorded old one 301s to the category's current URL instead of 404ing. */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $category = parent::resolveRouteBinding($value, $field);

        if ($category !== null || $field !== 'slug') {
            return $category;
        }

        $redirect = SlugRedirect::query()->where('entity_type', 'category')->where('old_slug', $value)->first();
        $moved = $redirect ? static::find($redirect->entity_id) : null;

        if ($moved) {
            throw new SlugMovedException($moved, 'category.show');
        }

        return null;
    }
}
