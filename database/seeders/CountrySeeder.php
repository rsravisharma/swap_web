<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'phone_code' => '+1', 'flag_emoji' => '🇺🇸', 'currency' => 'USD', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Canada', 'code' => 'CA', 'phone_code' => '+1', 'flag_emoji' => '🇨🇦', 'currency' => 'CAD', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'United Kingdom', 'code' => 'GB', 'phone_code' => '+44', 'flag_emoji' => '🇬🇧', 'currency' => 'GBP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'India', 'code' => 'IN', 'phone_code' => '+91', 'flag_emoji' => '🇮🇳', 'currency' => 'INR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Australia', 'code' => 'AU', 'phone_code' => '+61', 'flag_emoji' => '🇦🇺', 'currency' => 'AUD', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Germany', 'code' => 'DE', 'phone_code' => '+49', 'flag_emoji' => '🇩🇪', 'currency' => 'EUR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'France', 'code' => 'FR', 'phone_code' => '+33', 'flag_emoji' => '🇫🇷', 'currency' => 'EUR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Italy', 'code' => 'IT', 'phone_code' => '+39', 'flag_emoji' => '🇮🇹', 'currency' => 'EUR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spain', 'code' => 'ES', 'phone_code' => '+34', 'flag_emoji' => '🇪🇸', 'currency' => 'EUR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Brazil', 'code' => 'BR', 'phone_code' => '+55', 'flag_emoji' => '🇧🇷', 'currency' => 'BRL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Russia', 'code' => 'RU', 'phone_code' => '+7', 'flag_emoji' => '🇷🇺', 'currency' => 'RUB', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Japan', 'code' => 'JP', 'phone_code' => '+81', 'flag_emoji' => '🇯🇵', 'currency' => 'JPY', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'China', 'code' => 'CN', 'phone_code' => '+86', 'flag_emoji' => '🇨🇳', 'currency' => 'CNY', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'South Korea', 'code' => 'KR', 'phone_code' => '+82', 'flag_emoji' => '🇰🇷', 'currency' => 'KRW', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mexico', 'code' => 'MX', 'phone_code' => '+52', 'flag_emoji' => '🇲🇽', 'currency' => 'MXN', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Singapore', 'code' => 'SG', 'phone_code' => '+65', 'flag_emoji' => '🇸🇬', 'currency' => 'SGD', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Netherlands', 'code' => 'NL', 'phone_code' => '+31', 'flag_emoji' => '🇳🇱', 'currency' => 'EUR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'South Africa', 'code' => 'ZA', 'phone_code' => '+27', 'flag_emoji' => '🇿🇦', 'currency' => 'ZAR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('countries')->insert($countries);
    }
}
