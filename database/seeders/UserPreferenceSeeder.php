<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserPreference;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Default preferences for testing (optional)
        $defaultPreferences = [
            [
                'user_id' => 1,
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
