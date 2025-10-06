<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tables to reset auto-increment and avoid duplicates
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('property_images')->truncate();
        DB::table('property_videos')->truncate();
        DB::table('property_locations')->truncate();
        DB::table('properties')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Assume JSON file is located in database/json/
        $propertiesPath = base_path('database/json/properties.json');
        $propertiesData = json_decode(file_get_contents($propertiesPath), true);

        // Seed Properties, Locations, Images, Videos
        foreach ($propertiesData as $property) {
            // Insert Property (keep explicit id for main table)
            DB::table('properties')->insert([
                'id' => $property['id'],
                'title' => $property['title'],
                'slug' => $property['slug'],
                'description' => $property['description'],
                'price' => $property['price'],
                'currency' => $property['currency'],
                'discount' => $property['discount'],
                'discounted_price' => $property['discounted_price'],
                'type' => $property['type'],
                'purpose' => $property['purpose'],
                'property_type' => $property['property_type'],
                'bedrooms' => $property['bedrooms'],
                'bathrooms' => $property['bathrooms'],
                'living_rooms' => $property['living_rooms'],
                'kitchens' => $property['kitchens'],
                'balconies' => $property['balconies'],
                'area_total' => $property['area_total'],
                'features' => $property['features'], // JSON string
                'tags' => $property['tags'], // JSON string
                'floor' => $property['floor'],
                'total_floors' => $property['total_floors'],
                'furnishing' => $property['furnishing'],
                'status' => $property['status'],
                'owner_id' => $property['owner_id'],
                'agency_id' => $property['agency_id'],
                'views' => $property['views'],
                'likes' => $property['likes'],
                'is_featured' => $property['is_featured'],
                'created_at' => Carbon::parse($property['created_at'])->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($property['updated_at'])->format('Y-m-d H:i:s'),
            ]);

            // Insert Location (omit id to let auto-increment)
            if (isset($property['location'])) {
                $location = $property['location'];
                DB::table('property_locations')->insert([
                    // 'id' => $location['id'], // Omitted
                    'property_id' => $location['property_id'],
                    'city' => $location['city'],
                    'district' => $location['district'],
                    'street' => $location['street'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'landmark' => $location['landmark'],
                    'created_at' => Carbon::parse($location['created_at'])->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($location['updated_at'])->format('Y-m-d H:i:s'),
                ]);
            }

            // Insert Images (omit id to let auto-increment)
            if (isset($property['images']) && is_array($property['images'])) {
                foreach ($property['images'] as $image) {
                    DB::table('property_images')->insert([
                        // 'id' => $image['id'], // Omitted to avoid duplicates
                        'property_id' => $image['property_id'],
                        'image_url' => $image['image_url'],
                        'is_primary' => $image['is_primary'],
                        'created_at' => Carbon::parse($image['created_at'])->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($image['updated_at'])->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Insert Videos (omit id to let auto-increment)
            if (isset($property['videos']) && is_array($property['videos'])) {
                foreach ($property['videos'] as $video) {
                    DB::table('property_videos')->insert([
                        // 'id' => $video['id'], // Omitted
                        'property_id' => $video['property_id'],
                        'video_url' => $video['video_url'],
                        'created_at' => Carbon::parse($video['created_at'])->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($video['updated_at'])->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $this->command->info('Properties data seeded successfully!');
    }
}