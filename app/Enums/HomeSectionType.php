<?php

namespace App\Enums;

/** FR-STF-001: the fixed set of home page section types. */
enum HomeSectionType: string
{
    case HeroBanner = 'hero_banner';
    case CategoryGrid = 'category_grid';
    case FeaturedProducts = 'featured_products';
    case LatestProducts = 'latest_products';
    case BestSellers = 'best_sellers';
    case PromoBanner = 'promo_banner';
    case TrustBadges = 'trust_badges';
    case Newsletter = 'newsletter';

    public function defaultTitle(): string
    {
        return match ($this) {
            self::HeroBanner => 'Hero banner',
            self::CategoryGrid => 'Shop by category',
            self::FeaturedProducts => 'Featured',
            self::LatestProducts => 'New arrivals',
            self::BestSellers => 'Best sellers',
            self::PromoBanner => 'Promotion',
            self::TrustBadges => 'Why shop with us',
            self::Newsletter => 'Stay in touch',
        };
    }
}
