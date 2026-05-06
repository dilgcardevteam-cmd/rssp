<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobVacancySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('job_vacancies')->insert([
            'vacancy_id' => 'VAC-00001',
            'position_title' => 'Administrative Officer',
            'vacancy_type' => 'Plantilla',
            'monthly_salary' => 35000.00,
            'salary_grade' => 'SG-18',
            'place_of_assignment' => 'DILG-CAR, Baguio City',
            'status' => 'OPEN',
            'closing_date' => Carbon::now()->addDays(10),

            // Qualifications
            'qualification_education' => 'Bachelor’s degree relevant to the job',
            'qualification_training' => '8 hours of relevant training',
            'qualification_experience' => '2 years of relevant experience',
            'qualification_eligibility' => 'Career Service Professional',

            // Plantilla-specific fields
            'pcn_no' => 'PCN-1234',
            'plantilla_item_no' => 'PI-5678',
            'competencies' => json_encode(['Report Writing', 'Coordination Skills']),

            // COS fields (left null)
            'expected_output' => null,
            'scope_of_work' => null,
            'duration_of_work' => null,

            // Shared fields
            'to_position' => 'HRMO V',
            'to_office' => 'DILG Regional Office',
            'to_office_address' => 'Upper Session Road, Baguio City',

            'last_modified_by' => 'admin1',
            'last_modified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}


