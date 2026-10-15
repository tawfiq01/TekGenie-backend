<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Exceptions\SlugMovedException;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property ProductStatus $status
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'short_description', 'description',
        'status', 'is_featured', 'meta_title', 'meta_description', 'published_at',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'in_stock' => 'boolean',
            'rating_avg' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    /**
     * FR-CAT-011: visible on the storefront only when Active with at least
     * one active variant.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active)
            ->whereHas('variants', fn ($q) => $q->where('is_active', true));
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** @return BelongsToMany<OptionType, $this> */
    public function optionTypes(): BelongsToMany
    {
        return $this->belongsToMany(OptionType::class, 'product_option_type')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProductAttribute, $this> */
    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Product, $this> */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<WishlistItem, $this> */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /** @return BelongsToMany<Coupon, $this> */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    /** FR-SET-005: an unresolved slug that matches a recorded old one 301s to the product's current URL instead of 404ing. */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $product = parent::resolveRouteBinding($value, $field);

        if ($product !== null || $field !== 'slug') {
            return $product;
        }

        $redirect = SlugRedirect::query()->where('entity_type', 'product')->where('old_slug', $value)->first();
        $moved = $redirect ? static::find($redirect->entity_id) : null;

        if ($moved) {
            throw new SlugMovedException($moved, 'product.show');
        }

        return null;
    }
}
