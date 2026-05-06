<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JobVacancy;
use App\Models\User;
use App\Models\PersonalInformation;
use App\Models\Applications;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// 1. Create a sample Plantilla vacancy
$vacancy = JobVacancy::create([
    'position_title' => 'New Sample Plantilla Position',
    'vacancy_type' => 'Plantilla',
    'status' => 'OPEN',
    'closing_date' => Carbon::yesterday()->format('Y-m-d'),
    'monthly_salary' => 35000.00,
    'salary_grade' => 'SG 11',
    'place_of_assignment' => 'DILG Regional Office',
    'qualification_education' => 'Bachelor\'s degree',
    'qualification_training' => 'None required',
    'qualification_experience' => 'None required',
    'qualification_eligibility' => 'CS Professional',
    'expected_output' => 'TBD',
    'scope_of_work' => 'TBD',
    'duration_of_work' => 'Permanent',
    'to_person' => 'Juan Dela Cruz',
    'to_position' => 'Regional Director',
    'to_office' => 'DILG CAR',
    'to_office_address' => 'Baguio City',
]);

echo 'Created Vacancy: ' . $vacancy->vacancy_id . "\n";

// 2. Create 5 Applicants and apply to this position
for ($i = 1; $i <= 5; $i++) {
    // Create User
    $user = User::create([
        'name' => 'SimApplicant Test ' . $i,
        'username' => 'applicant_sim_'. time() . '_' . $i,
        'email' => 'applicant_sim_'. time() . '_' . $i .'@example.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => Carbon::now(),
    ]);

    // Create Personal Information
    PersonalInformation::create([
        'user_id' => $user->id,
        'first_name' => 'SimApplicant',
        'last_name' => 'Test ' . $i,
        'middle_name' => 'M',
        'name_extension' => '',
        'date_of_birth' => '1990-01-01',
        'place_of_birth' => 'Baguio City',
        'sex' => 'Male',
        'civil_status' => 'Single',
        'height' => '1.7',
        'weight' => '65',
        'blood_type' => 'O+',
        'gsis_id_no' => '',
        'pag_ibig_id_no' => '',
        'philhealth_no' => '',
        'sss_no' => '',
        'tin_no' => '',
        'agency_employee_no' => '',
        'citizenship' => 'Filipino',
    ]);

    // Create Application
    Applications::create([
        'user_id' => $user->id,
        'vacancy_id' => $vacancy->vacancy_id,
        'status' => 'Pending',
        'date_submitted' => Carbon::now()->format('Y-m-d H:i:s'),
        'application_type' => 'New',
        'tracking_code' => 'SIM-'.Str::random(10),
    ]);

    echo 'Created Applicant & Application for User: ' . $user->email . "\n";
}

echo "Simulation complete!\n";
