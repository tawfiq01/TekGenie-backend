<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Seo\SeoMetaService;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-STF-007; FR-SET-002. */
class StaticPageController extends Controller
{
    public function __construct(private readonly SeoMetaService $seoMeta) {}

    public function show(Page $page): View
    {
        if (! $page->is_published) {
            throw new NotFoundHttpException;
        }

        $seo = $this->seoMeta->forPage($page)->withJsonLd([
            $this->seoMeta->breadcrumbJsonLd([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $page->title],
            ]),
        ]);

        return view('storefront.page', ['page' => $page, 'seo' => $seo]);
    }
}
