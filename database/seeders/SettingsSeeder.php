<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'clinic_name', 'value' => 'Care Plus Clinic'],
            ['key' => 'registration_number', 'value' => 'REG-2025-88492'],
            ['key' => 'primary_email', 'value' => 'contact@careplus.com'],
            ['key' => 'contact_phone', 'value' => '+94 77 123 4567'],
            ['key' => 'address', 'value' => '123 Health Avenue, Medical District, Cityville'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
