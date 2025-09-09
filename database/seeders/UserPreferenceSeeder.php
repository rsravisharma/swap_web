<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserPreference;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Check if any users exist
        $userCount = User::count();

        if ($userCount === 0) {
            $this->command->info('No users found. Creating a test user first...');

            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
        } else {
            // Use the first existing user
            $user = User::first();
            $this->command->info("Using existing user: {$user->email}");
        }
        // Default preferences for testing (optional)
        $defaultPreferences = [
            [
                'user_id' => $user->id,
                'key' => 'notification_settings',
                'value' => [
                    'push_notifications' => true,
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'marketing_notifications' => true,
                ],
            ],
            [
                'user_id' => 1,
                'key' => 'privacy_settings',
                'value' => [
                    'show_phone_number' => false,
                    'show_email' => false,
                    'show_location' => true,
                    'show_online_status' => true,
                ],
            ],
        ];

        foreach ($defaultPreferences as $preference) {
            UserPreference::create($preference);
        }
    }
}
