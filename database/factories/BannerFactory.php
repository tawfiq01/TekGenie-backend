<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'placement' => 'hero',
            'title' => fake()->sentence(3),
            'image_desktop_path' => 'banners/placeholder.jpg',
            'alt_text' => fake()->sentence(3),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
