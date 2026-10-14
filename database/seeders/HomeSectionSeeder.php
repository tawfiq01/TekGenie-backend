<?php

namespace Database\Seeders;

use App\Enums\HomeSectionType;
use App\Models\HomeSection;
use Illuminate\Database\Seeder;

/**
 * Seeds the fixed set of home page sections (FR-STF-001). Hero and promo
 * banners pull from the banners table by placement; featured/latest/best
 * sellers/category grid are computed from the catalogue — none of these
 * need admin-picked items to show something reasonable out of the box.
 */
class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            HomeSectionType::HeroBanner,
            HomeSectionType::CategoryGrid,
            HomeSectionType::FeaturedProducts,
            HomeSectionType::LatestProducts,
            HomeSectionType::BestSellers,
            HomeSectionType::PromoBanner,
            HomeSectionType::TrustBadges,
            HomeSectionType::Newsletter,
        ];

        foreach ($sections as $position => $type) {
            HomeSection::query()->firstOrCreate(
                ['type' => $type->value],
                ['title' => $type->defaultTitle(), 'sort_order' => $position, 'is_active' => true]
            );
        }
    }
}
