<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Load users from JSON file
        $users = json_decode(file_get_contents(base_path('database/json/users.json')), true);

        // Check if JSON data is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error decoding users.json: ' . json_last_error_msg());
            return;
        }

        // Seed users
        foreach ($users as $user) {
            // Create user record
            User::updateOrCreate(
                ['id' => $user['id']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => $user['email_verified_at'],
                    'password' => Hash::make($user['password']),
                    'confirm_password' => Hash::make($user['confirm_password']),
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'google_id' => $user['google_id'],
                    'address' => $user['address'],
                    'remember_token' => $user['remember_token'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ]
            );
        }
    }
}