<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'badge' => 'normal',
                'monthly_price' => 0,
                'annual_price' => 0,
                'monthly_slots' => 0,
                'allowed_pdf_uploads' => false,
                'coins_monthly' => 0,
                'description' => 'Basic plan for casual users',
            ],
            [
                'name' => 'Silver',
                'badge' => 'silver',
                'monthly_price' => 19,
                'annual_price' => 199,
                'monthly_slots' => 4,
                'allowed_pdf_uploads' => false,
                'coins_monthly' => 40,
                'description' => 'Perfect for regular sellers',
            ],
            [
                'name' => 'Gold',
                'badge' => 'gold',
                'monthly_price' => 199,
                'annual_price' => 1999,
                'monthly_slots' => 40,
                'allowed_pdf_uploads' => false,
                'coins_monthly' => 400,
                'description' => 'For power sellers',
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
