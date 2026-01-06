<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $this->call([
            AdminSeeder::class,
            CountrySeeder::class,
            UserPreferenceSeeder::class,
            CategoryHierarchySeeder::class,
            PaymentMethodSeeder::class,
            LocationSeeder::class,
            SubscriptionPlanSeeder::class,
            PdfBookSeeder::class,
            BlogPostSeeder::class,
        ]);
    }
}
