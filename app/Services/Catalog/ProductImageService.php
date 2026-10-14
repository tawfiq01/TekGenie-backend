<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Product image upload pipeline (FR-CAT-007/008; VR-10; NFR-SEC-08). Images
 * are renamed to random names, resized into standard WebP sizes with a JPEG
 * fallback, and the original is re-encoded with metadata stripped.
 */
class ProductImageService
{
    private readonly ImageManager $manager;

    public function __construct(private readonly AuditLogger $audit)
    {
        $this->manager = new ImageManager(Driver::class);
    }

    public function upload(Product $product, UploadedFile $file, ?int $optionValueId, ?string $altText): ProductImage
    {
        // VR-10 allows uploads up to 6000×6000; GD needs real headroom to
        // decode and clone bitmaps that large (6000×6000 RGBA ≈ 137 MB each).
        ini_set('memory_limit', '512M');

        $source = $this->manager->decode($file);
        $base = 'products/'.Str::random(32);

        foreach (config('catalog.image_sizes') as $size => $spec) {
            $variant = clone $source;
            $variant = $spec['fit'] === 'cover'
                ? $variant->cover($spec['width'], $spec['height'])
                : $variant->scaleDown($spec['width'], $spec['height']);

            $encoded = $variant->encode(new WebpEncoder(quality: config('catalog.image_webp_quality'), strip: true));
            Storage::disk('public')->put("{$base}-{$size}.webp", (string) $encoded);
        }

        $fallbackSpec = config('catalog.image_sizes.card');
        $fallback = (clone $source)->cover($fallbackSpec['width'], $fallbackSpec['height']);
        $fallbackEncoded = $fallback->encode(new JpegEncoder(quality: config('catalog.image_fallback_quality'), strip: true));
        Storage::disk('public')->put("{$base}-fallback.jpg", (string) $fallbackEncoded);

        $image = $product->images()->create([
            'option_value_id' => $optionValueId,
            'path' => $base,
            'alt_text' => $altText,
            'width' => min($source->width(), 65535),
            'height' => min($source->height(), 65535),
            'is_main' => ! $product->images()->exists(),
            'sort_order' => ($product->images()->max('sort_order') ?? -1) + 1,
        ]);

        $this->audit->log('admin.product-image.uploaded', $image, ['product_id' => $product->id]);

        return $image;
    }

    public function setMain(ProductImage $image): void
    {
        $image->product->images()->update(['is_main' => false]);
        $image->forceFill(['is_main' => true])->save();

        $this->audit->log('admin.product-image.set-main', $image);
    }

    /** @param  array<string, mixed>  $data */
    public function updateMeta(ProductImage $image, array $data): ProductImage
    {
        $image->forceFill([
            'alt_text' => $data['alt_text'] ?? null,
            'option_value_id' => $data['option_value_id'] ?? null,
        ])->save();

        $this->audit->log('admin.product-image.updated', $image);

        return $image;
    }

    /** @param  array<int, int>  $orderedImageIds */
    public function reorder(Product $product, array $orderedImageIds): void
    {
        foreach ($orderedImageIds as $position => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $position]);
        }
    }

    public function destroy(ProductImage $image): void
    {
        foreach (array_keys(config('catalog.image_sizes')) as $size) {
            Storage::disk('public')->delete("{$image->path}-{$size}.webp");
        }
        Storage::disk('public')->delete("{$image->path}-fallback.jpg");

        $wasMain = $image->is_main;
        $product = $image->product;

        $image->delete();

        if ($wasMain) {
            $product->images()->first()?->update(['is_main' => true]);
        }

        $this->audit->log('admin.product-image.deleted', null, ['product_id' => $product->id, 'path' => $image->path]);
    }
}
