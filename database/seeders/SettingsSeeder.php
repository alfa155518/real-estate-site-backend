<?php

namespace Database\Seeders;

use App\Models\Admin\Settings;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load reviews from JSON file
        $settings = json_decode(file_get_contents(base_path('database/json/settings.json')), true);

        // Check if JSON data is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Error decoding settings.json: ' . json_last_error_msg());
            return;
        }

        Settings::firstOrCreate(
            $settings['settings']
        );
    }
}
