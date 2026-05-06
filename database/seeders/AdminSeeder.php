<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate([
            'email' => 'admin@debug.com'
        ], [
            'username' => 'debug_admin',
            'name' => 'Debug Admin',
            'office' => 'IT',
            'designation' => 'Developer',
            'email' => 'admin@debug.com',
            'password' => Hash::make('password123'),
            'role' => 'admin', // or 'viewer'
            'is_active' => true
        ]);
    }
}
