<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-STF-007. */
class StaticPageController extends Controller
{
    public function show(Page $page): View
    {
        if (! $page->is_published) {
            throw new NotFoundHttpException;
        }

        return view('storefront.page', ['page' => $page]);
    }
}
