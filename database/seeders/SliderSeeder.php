<?php

namespace Database\Seeders;

use App\Models\Sliders;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load reviews from JSON file
        $sliders = json_decode(file_get_contents(base_path('database/json/sliders.json')), true);

        // Check if JSON data is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Error decoding Sliders.json: ' . json_last_error_msg());
            return;
        }

        foreach ($sliders['sliders'] as $sliderData) {
            Sliders::create([
                'name' => $sliderData['name'],
                'images' => $sliderData['images']
            ]);
        }

    }
}
