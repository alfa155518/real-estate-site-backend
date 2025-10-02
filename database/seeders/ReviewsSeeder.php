<?php

namespace Database\Seeders;


use App\Models\Reviews;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        // Load reviews from JSON file
        $reviews = json_decode(file_get_contents(base_path('database/json/reviews.json')), true);

        // Check if JSON data is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error decoding reviews.json: ' . json_last_error_msg());
            return;
        }

        // Seed reviews
        foreach ($reviews as $review) {
            // Create review record
            Reviews::updateOrCreate(
                ['id' => $review['id']],
                [
                    'user_id' => $review['user_id'],
                    'property_id' => $review['property_id'],
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'likes' => [],
                    'created_at' => $review['created_at'],
                    'updated_at' => $review['updated_at'],
                ]
            );
        }
    }
}