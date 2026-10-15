<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/** FR-SET-005: records the old slug whenever a Product/Category/Page's slug changes. */
class SlugRedirectObserver
{
    public function updating(Model $model): void
    {
        $originalSlug = $model->getOriginal('slug');

        if (! $model->isDirty('slug') || $originalSlug === null) {
            return;
        }

        SlugRedirect::query()->updateOrCreate(
            ['entity_type' => $this->entityType($model), 'old_slug' => $originalSlug],
            ['entity_id' => $model->getKey()]
        );
    }

    private function entityType(Model $model): string
    {
        return match ($model::class) {
            Product::class => 'product',
            Category::class => 'category',
            Page::class => 'page',
            default => throw new LogicException('Unsupported slug-redirect entity: '.$model::class),
        };
    }
}
