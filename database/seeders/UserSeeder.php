<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users table
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => true,
                'is_super_admin' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'is_super_admin' => false,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed password_reset_tokens table
        DB::table('password_reset_tokens')->insert([
            [
                'email' => 'user@example.com',
                'token' => Str::random(60),
                'created_at' => now(),
            ],
        ]);

        // Seed sessions table
        DB::table('sessions')->insert([
            [
                'id' => Str::random(40),
                'user_id' => 0, // Assuming the first user is the admin
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'payload' => 'session_payload_data',
                'last_activity' => time(),
            ],
        ]);
    }
}
