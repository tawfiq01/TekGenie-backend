<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\HomeSectionType;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * FR-STF-001/002.
 *
 * NFR-PERF-05: the assembled section data is cached for 10 minutes (long
 * enough to matter, short enough to still pick up a banner's scheduled
 * start/end and newly-published/featured products without a save event),
 * and invalidated immediately on any HomeSection/Banner change.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $result = Cache::remember('storefront.home', now()->addMinutes(10), function (): array {
            $sections = HomeSection::query()->where('is_active', true)->orderBy('sort_order')->get();

            $data = [];

            foreach ($sections as $section) {
                $data[$section->type->value] = match ($section->type) {
                    HomeSectionType::HeroBanner => $this->scheduledBanners('hero'),
                    HomeSectionType::PromoBanner => $this->scheduledBanners('promo'),
                    HomeSectionType::CategoryGrid => Category::activeTopLevel()->take(8)->values(),
                    HomeSectionType::FeaturedProducts => Product::visible()->with('images')->where('is_featured', true)->latest('published_at')->limit(8)->get(),
                    HomeSectionType::LatestProducts => Product::visible()->with('images')->latest('published_at')->limit(8)->get(),
                    HomeSectionType::BestSellers => Product::visible()->with('images')->orderByDesc('sold_count_30d')->limit(8)->get(),
                    HomeSectionType::TrustBadges, HomeSectionType::Newsletter => null,
                };
            }

            return ['sections' => $sections, 'data' => $data];
        });

        return view('storefront.home', $result);
    }

    /** @return Collection<int, Banner> */
    private function scheduledBanners(string $placement)
    {
        return Banner::query()
            ->where('placement', $placement)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Banner $banner) => $banner->isCurrentlyScheduled())
            ->values();
    }
}
