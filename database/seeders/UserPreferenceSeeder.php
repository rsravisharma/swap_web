<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPreference;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Hash;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCount = User::count();

        if ($userCount === 0) {
            $this->command->info('No users found. Creating a test user first...');

            $planId = SubscriptionPlan::first()?->id;
            $user = User::create([
                'name' => 'The Digital Curator',
                'email' => 'digital@curator.com', 
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'coins' => 50,
                'subscription_plan_id' => $planId,
                'referral_code' => User::generateUniqueReferralCode()
            ]);
        } else {
            $user = User::first();
            $this->command->info("Using existing user: {$user->email}");
        }

        // Default preferences setup
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
                'user_id' => $user->id,
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
