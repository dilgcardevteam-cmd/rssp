<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ExamDetail;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\ExamItems;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;




class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // -----sample verified user
        User::factory()->create([
            'name' => "sample",
            'email' => "sample1@example.com",
            'password' => "samplepass",
        ]);

        // -----sample non-verified user
        User::factory()->create([
            'name' => "sample_uv",
            'email' => "sample2@example.com",
            'password' => "samplepass",
            "email_verified_at" => null
        ]);

        // -----sample admin user
        User::factory()->create([
            'name' => "admin",
            'email' => "sample3@example.com",
            'password' => "samplepass",
            "is_admin" => true
        ]);

        // -----sample admin user
        User::factory()->create([
            'name' => "superadmin",
            'email' => "sample4@example.com",
            'password' => "samplepass",
            "is_admin" => true,
            "is_super_admin" => true
        ]);

        // -----sample admin user
        User::factory()->create([
            'name' => "pagso",
            'email' => "pagso@gmail.com",
            'password' => "123456",
            "is_admin" => false,
            "is_super_admin" => false
        ]);


        JobVacancy::create([
            'position_title' => 'Software Developer II',
            'vacancy_id' => 'DEV-007',
            'vacancy_type' => 'Permanent',
            'monthly_salary' => 45000,
            'place_of_assignment' => 'IT Department, Main Office',
            'status' => 'OPEN',
            'closing_date' => now()->addWeeks(2),
            'qualification_education' => 'Bachelor’s Degree in Computer Science',
            'qualification_training' => '8 hours of relevant training',
            'qualification_experience' => '1 year of relevant experience',
            'qualification_eligibility' => 'CS Professional',
            'scope_of_work' => 'Develop and maintain web applications',
            'expected_output' => 'Fully functional systems',
            'duration_of_work' => 'Indefinite',
            'to_person' => 'Engr. Juan Dela Cruz',
            'to_position' => 'HR Manager',
            'to_office' => 'Human Resources Division',
            'to_office_address' => '123 Government St., Metro Manila',
        ]);

        User::factory()->count(50)->create();

        JobVacancy::factory()->count(10)->create();

        ExamDetail::factory()->count(5)->create([
            'vacancy_id' => 'DEV-001'
        ]);

        ExamItems::factory()->count(5)->create([
            'vacancy_id' => 'DEV-001'
        ]);

        Applications::factory()->count(5)->create();

        Applications::factory()->count(5)->create([
            'result' => 0
        ]);


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

        Admin::updateOrCreate([
            'email' => 'admin@example.com'
        ], [
            'username' => 'admin',
            'name' => 'Admin',
            'office' => 'IT',
            'designation' => 'Developer',
            'email' => 'admin@example.com',
            'password' => '$2y$10$TgwEl/UczmxDyNiKSGnep.RA8qvqje1Ny25W1L7t.lrtFO7RR6ssi',
            'role' => 'admin', // or 'viewer'
            'is_active' => true
        ]);

    }
}
