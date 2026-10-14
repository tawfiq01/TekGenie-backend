<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds default settings (FR-SET-001). Values here are safe placeholders;
 * the business replaces them through Admin › Settings before launch.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store' => [
                'name' => '[Store Name]',
                'logo_path' => null,
                'favicon_path' => null,
                'contact_phone' => '',
                'contact_email' => '',
                'contact_address' => '',
                'business_hours' => '',
                'order_number_prefix' => 'ST',
                'currency_symbol' => '৳',
                'low_stock_default_threshold' => '5',
                'announcement_text' => '',
                'announcement_link' => '',
            ],
            'checkout' => [
                'min_order_value' => '0',
                'cod_max_order_value' => '0',
                'per_line_max_quantity' => '10',
                'guest_cart_purge_days' => '30',
            ],
            'shipping' => [
                'allow_unassigned_delivery' => '0',
            ],
            'seo' => [
                'default_meta_title_pattern' => '{title} | [Store Name]',
                'default_meta_description' => '',
            ],
            'notifications' => [
                'staff_new_order_email' => '',
                'status_change_emails_enabled' => '1',
            ],
        ];

        foreach ($defaults as $group => $settings) {
            foreach ($settings as $key => $value) {
                Setting::query()->firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => 'string']
                );
            }
        }
    }
}
