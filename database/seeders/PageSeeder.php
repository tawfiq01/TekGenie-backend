<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds the static page shells required by FR-STF-007. Bodies are placeholder
 * text only — the business supplies real policy wording before launch (BRD §6.3).
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'About Us', 'slug' => 'about'],
            ['title' => 'Contact Us', 'slug' => 'contact'],
            ['title' => 'FAQ', 'slug' => 'faq'],
            ['title' => 'Privacy Policy', 'slug' => 'privacy'],
            ['title' => 'Terms & Conditions', 'slug' => 'terms'],
            ['title' => 'Return & Refund Policy', 'slug' => 'returns'],
            ['title' => 'Shipping Policy', 'slug' => 'shipping'],
        ];

        foreach ($pages as $page) {
            Page::query()->firstOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'body' => '<p>[Policy text to be provided]</p>',
                    'is_published' => false,
                    'show_in_footer' => true,
                ]
            );
        }
    }
}
