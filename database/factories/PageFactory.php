<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
            'show_in_footer' => true,
        ];
    }
}
