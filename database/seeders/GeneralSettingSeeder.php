<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GeneralSetting::create([
            'company_name' => 'Bashir Sons Travels',
            'email' => 'info@bashirsons.com',
            'phone' => '+92 300 1234567',
            'alternate_phone' => '+92 300 7654321',
            'address' => '123 Main Street, Lahore',
            'city' => 'Lahore',
            'country' => 'Pakistan',
            'website_url' => 'https://bashirsons.com',
            'logo' => 'settings/logo.png',
            'favicon' => 'settings/favicon.ico',
            'facebook_url' => 'https://facebook.com/bashirsons',
            'instagram_url' => 'https://instagram.com/bashirsons',
            'business_hours' => 'Mon–Sat 9:00 AM – 6:00 PM',
        ]);
    }
}
