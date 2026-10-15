<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;

/** FR-SET-004: admin/account/cart/checkout stay disallowed regardless of what staff add below them. */
class RobotsController extends Controller
{
    public function show(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
        ];

        $extra = trim((string) Setting::get('seo', 'robots_txt_extra', ''));

        if ($extra !== '') {
            $lines[] = '';
            $lines[] = $extra;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
