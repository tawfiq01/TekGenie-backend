<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapGenerator;
use Illuminate\Http\Response;

/** FR-SET-003. */
class SitemapController extends Controller
{
    public function __construct(private readonly SitemapGenerator $sitemap) {}

    public function show(): Response
    {
        return response($this->sitemap->xml(), 200)->header('Content-Type', 'application/xml');
    }
}
