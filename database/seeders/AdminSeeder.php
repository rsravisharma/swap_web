<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@swap.com',
            'password' => Hash::make('password'), 
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Admin User',
            'email' => 'support@swap.com',
            'password' => Hash::make('password'), 
            'role' => 'admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Pdf Admin',
            'email' => 'pdf@swap.com',
            'password' => Hash::make('password'), 
            'role' => 'manager',
            'is_active' => true,
        ]);
    }
}
