<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\HomeSectionType;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

/** FR-STF-001/002. */
class HomeController extends Controller
{
    public function index(): View
    {
        $sections = HomeSection::query()->where('is_active', true)->orderBy('sort_order')->get();

        $data = [];

        foreach ($sections as $section) {
            $data[$section->type->value] = match ($section->type) {
                HomeSectionType::HeroBanner => $this->scheduledBanners('hero'),
                HomeSectionType::PromoBanner => $this->scheduledBanners('promo'),
                HomeSectionType::CategoryGrid => Category::query()->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->limit(8)->get(),
                HomeSectionType::FeaturedProducts => Product::visible()->with('images')->where('is_featured', true)->latest('published_at')->limit(8)->get(),
                HomeSectionType::LatestProducts => Product::visible()->with('images')->latest('published_at')->limit(8)->get(),
                HomeSectionType::BestSellers => Product::visible()->with('images')->orderByDesc('sold_count_30d')->limit(8)->get(),
                HomeSectionType::TrustBadges, HomeSectionType::Newsletter => null,
            };
        }

        return view('storefront.home', [
            'sections' => $sections,
            'data' => $data,
        ]);
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
