<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Bangladesh's 8 divisions and 64 districts (FR-SHP-001).
 */
class BangladeshGeoSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            'Dhaka' => [
                'name_bn' => 'ঢাকা',
                'districts' => [
                    'Dhaka' => 'ঢাকা',
                    'Faridpur' => 'ফরিদপুর',
                    'Gazipur' => 'গাজীপুর',
                    'Gopalganj' => 'গোপালগঞ্জ',
                    'Kishoreganj' => 'কিশোরগঞ্জ',
                    'Madaripur' => 'মাদারীপুর',
                    'Manikganj' => 'মানিকগঞ্জ',
                    'Munshiganj' => 'মুন্সিগঞ্জ',
                    'Narayanganj' => 'নারায়ণগঞ্জ',
                    'Narsingdi' => 'নরসিংদী',
                    'Rajbari' => 'রাজবাড়ী',
                    'Shariatpur' => 'শরীয়তপুর',
                    'Tangail' => 'টাঙ্গাইল',
                ],
            ],
            'Chattogram' => [
                'name_bn' => 'চট্টগ্রাম',
                'districts' => [
                    'Bandarban' => 'বান্দরবান',
                    'Brahmanbaria' => 'ব্রাহ্মণবাড়িয়া',
                    'Chandpur' => 'চাঁদপুর',
                    'Chattogram' => 'চট্টগ্রাম',
                    'Cumilla' => 'কুমিল্লা',
                    "Cox's Bazar" => 'কক্সবাজার',
                    'Feni' => 'ফেনী',
                    'Khagrachhari' => 'খাগড়াছড়ি',
                    'Lakshmipur' => 'লক্ষ্মীপুর',
                    'Noakhali' => 'নোয়াখালী',
                    'Rangamati' => 'রাঙ্গামাটি',
                ],
            ],
            'Rajshahi' => [
                'name_bn' => 'রাজশাহী',
                'districts' => [
                    'Bogura' => 'বগুড়া',
                    'Joypurhat' => 'জয়পুরহাট',
                    'Naogaon' => 'নওগাঁ',
                    'Natore' => 'নাটোর',
                    'Chapainawabganj' => 'চাঁপাইনবাবগঞ্জ',
                    'Pabna' => 'পাবনা',
                    'Rajshahi' => 'রাজশাহী',
                    'Sirajganj' => 'সিরাজগঞ্জ',
                ],
            ],
            'Khulna' => [
                'name_bn' => 'খুলনা',
                'districts' => [
                    'Bagerhat' => 'বাগেরহাট',
                    'Chuadanga' => 'চুয়াডাঙ্গা',
                    'Jashore' => 'যশোর',
                    'Jhenaidah' => 'ঝিনাইদহ',
                    'Khulna' => 'খুলনা',
                    'Kushtia' => 'কুষ্টিয়া',
                    'Magura' => 'মাগুরা',
                    'Meherpur' => 'মেহেরপুর',
                    'Narail' => 'নড়াইল',
                    'Satkhira' => 'সাতক্ষীরা',
                ],
            ],
            'Barishal' => [
                'name_bn' => 'বরিশাল',
                'districts' => [
                    'Barguna' => 'বরগুনা',
                    'Barishal' => 'বরিশাল',
                    'Bhola' => 'ভোলা',
                    'Jhalokati' => 'ঝালকাঠি',
                    'Patuakhali' => 'পটুয়াখালী',
                    'Pirojpur' => 'পিরোজপুর',
                ],
            ],
            'Sylhet' => [
                'name_bn' => 'সিলেট',
                'districts' => [
                    'Habiganj' => 'হবিগঞ্জ',
                    'Moulvibazar' => 'মৌলভীবাজার',
                    'Sunamganj' => 'সুনামগঞ্জ',
                    'Sylhet' => 'সিলেট',
                ],
            ],
            'Rangpur' => [
                'name_bn' => 'রংপুর',
                'districts' => [
                    'Dinajpur' => 'দিনাজপুর',
                    'Gaibandha' => 'গাইবান্ধা',
                    'Kurigram' => 'কুড়িগ্রাম',
                    'Lalmonirhat' => 'লালমনিরহাট',
                    'Nilphamari' => 'নীলফামারী',
                    'Panchagarh' => 'পঞ্চগড়',
                    'Rangpur' => 'রংপুর',
                    'Thakurgaon' => 'ঠাকুরগাঁও',
                ],
            ],
            'Mymensingh' => [
                'name_bn' => 'ময়মনসিংহ',
                'districts' => [
                    'Jamalpur' => 'জামালপুর',
                    'Mymensingh' => 'ময়মনসিংহ',
                    'Netrokona' => 'নেত্রকোনা',
                    'Sherpur' => 'শেরপুর',
                ],
            ],
        ];

        DB::transaction(function () use ($divisions) {
            $divisionSort = 0;

            foreach ($divisions as $divisionName => $division) {
                $divisionId = DB::table('divisions')->insertGetId([
                    'name' => $divisionName,
                    'name_bn' => $division['name_bn'],
                    'sort_order' => $divisionSort++,
                ]);

                $districtSort = 0;

                foreach ($division['districts'] as $districtName => $districtNameBn) {
                    DB::table('districts')->insert([
                        'division_id' => $divisionId,
                        'name' => $districtName,
                        'name_bn' => $districtNameBn,
                        'delivery_zone_id' => null,
                        'is_active' => true,
                    ]);

                    $districtSort++;
                }
            }
        });
    }
}
