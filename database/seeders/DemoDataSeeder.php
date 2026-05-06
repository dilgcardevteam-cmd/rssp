<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\UploadedDocument;
use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Job Vacancies
        // Plantilla Job
        $plantillaJob = JobVacancy::create([
            'position_title' => 'Administrative Officer V',
            'vacancy_type' => 'Plantilla',
            'closing_date' => now()->addMonth(),
            'status' => 'OPEN',
            'monthly_salary' => 50000.00,
            'salary_grade' => 18,
            'place_of_assignment' => 'Finance Division',
            'qualification_education' => 'Bachelor\'s Degree relevant to the job',
            'qualification_training' => '8 hours of relevant training',
            'qualification_experience' => '2 years of relevant experience',
            'qualification_eligibility' => 'Career Service Professional',
            'to_person' => 'Director Jane Doe',
            'to_position' => 'Regional Director',
            'to_office' => 'DILG CAR',
            'to_office_address' => 'Baguio City',
        ]);
        // Note: vacancy_id is auto-generated in boot()

        // COS Job
        $cosJob = JobVacancy::create([
            'position_title' => 'Information Systems Analyst I',
            'vacancy_type' => 'COS',
            'closing_date' => now()->addMonth(),
            'status' => 'OPEN',
            'monthly_salary' => 27000.00,
            'salary_grade' => 11,
            'place_of_assignment' => 'ORD - ICT Unit',
            'qualification_education' => 'Bachelor\'s Degree in IT/CS',
            'qualification_training' => 'None required',
            'qualification_experience' => 'None required',
            'qualification_eligibility' => 'None required', // COS usually doesn't strictly require eligibility in same way
            'expected_output' => 'Maintained Network Systems',
            'to_person' => 'Director Jane Doe',
            'to_position' => 'Regional Director',
            'to_office' => 'DILG CAR',
            'to_office_address' => 'Baguio City',
        ]);

        // 2. Create Users (Applicants)
        // Plantilla Applicant
        $plantillaUser = User::firstOrCreate(
            ['email' => 'applicant_plantilla@demo.com'],
            [
                'name' => 'Juan Plantilla',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        PersonalInformation::updateOrCreate(
            ['user_id' => $plantillaUser->id],
            [
                'first_name' => 'Juan',
                'surname' => 'Plantilla',
                'middle_name' => 'Dela',
                'name_extension' => '',
                'date_of_birth' => '1990-01-01',
                'sex' => 'Male',
                'civil_status' => 'Single',
                'mobile_no' => '09123456789',
                'email_address' => 'applicant_plantilla@demo.com',
            ]
        );

        // COS Applicant
        $cosUser = User::firstOrCreate(
            ['email' => 'applicant_cos@demo.com'],
            [
                'name' => 'Maria COS',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        PersonalInformation::updateOrCreate(
            ['user_id' => $cosUser->id],
            [
                'first_name' => 'Maria',
                'surname' => 'COS',
                'middle_name' => 'Santos',
                'name_extension' => '',
                'date_of_birth' => '1995-05-05',
                'sex' => 'Female',
                'civil_status' => 'Married',
                'mobile_no' => '09987654321',
                'email_address' => 'applicant_cos@demo.com',
            ]
        );

        // 3. Create Applications and Documents

        // Application 1: Plantilla (Pending)
        $app1 = Applications::create([
            'user_id' => $plantillaUser->id,
            'vacancy_id' => $plantillaJob->vacancy_id,
            'status' => 'Pending',
        ]);

        // Documents for Plantilla (Needs PDS, Eligibility, Transcript, Diploma, Training)
        $plantillaDocs = [
            'application_letter' => 'Pending',
            'signed_pds' => 'Pending',
            'cert_eligibility' => 'Pending',
            'transcript_records' => 'Pending',
            'photocopy_diploma' => 'Pending',
            'cert_training' => 'Pending',
            'signed_work_exp_sheet' => 'Pending', // Often required too
        ];

        foreach ($plantillaDocs as $type => $status) {
            UploadedDocument::create([
                'user_id' => $plantillaUser->id,
                'document_type' => $type,
                'original_name' => "demo_{$type}.pdf",
                'stored_name' => "demo_{$type}.pdf",
                'storage_path' => "demo/path/{$type}.pdf", // Fake path
                'mime_type' => 'application/pdf',
                'file_size_8b' => 1024,
                'status' => $status,
                'remarks' => '',
            ]);
        }


        // Application 2: COS (Compliance / Needs Revision)
        $app2 = Applications::create([
            'user_id' => $cosUser->id,
            'vacancy_id' => $cosJob->vacancy_id,
            'status' => 'Pending', // Will become Compliance if we set doc status
        ]);

        // Documents for COS (Needs PDS, Transcript, Diploma, Work Exp)
        // Let's make one "Needs Revision" to test Compliance tab
        $cosDocs = [
            'application_letter' => 'Verified',
            'signed_pds' => 'Needs Revision', // This should trigger Compliance flow
            'transcript_records' => 'Verified',
            'photocopy_diploma' => 'Verified',
            'signed_work_exp_sheet' => 'Verified',
        ];

        foreach ($cosDocs as $type => $status) {
            UploadedDocument::create([
                'user_id' => $cosUser->id,
                'document_type' => $type,
                'original_name' => "demo_{$type}.pdf",
                'stored_name' => "demo_{$type}.pdf",
                'storage_path' => "demo/path/{$type}.pdf",
                'mime_type' => 'application/pdf',
                'file_size_8b' => 1024,
                'status' => $status,
                'remarks' => ($status === 'Needs Revision') ? 'Please sign page 2.' : '',
            ]);
        }
        
        // 4. Create 10 Additional Plantilla Applicants
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => "plantilla_applicant_{$i}@demo.com"],
                [
                    'name' => "Plantilla Applicant {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            PersonalInformation::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => "Plantilla{$i}",
                    'surname' => "Applicant",
                    'middle_name' => "Test",
                    'name_extension' => '',
                    'date_of_birth' => '1992-01-01',
                    'sex' => ($i % 2 == 0) ? 'Female' : 'Male',
                    'civil_status' => 'Single',
                    'mobile_no' => '0912345678' . $i,
                    'email_address' => "plantilla_applicant_{$i}@demo.com",
                ]
            );

            Applications::create([
                'user_id' => $user->id,
                'vacancy_id' => $plantillaJob->vacancy_id,
                'status' => 'Pending',
            ]);

            foreach ($plantillaDocs as $type => $status) {
                UploadedDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $type,
                    'original_name' => "demo_{$type}_{$i}.pdf",
                    'stored_name' => "demo_{$type}_{$i}.pdf",
                    'storage_path' => "demo/path/{$type}.pdf", // Reusing the same dummy file path
                    'mime_type' => 'application/pdf',
                    'file_size_8b' => 1024,
                    'status' => 'Pending',
                    'remarks' => '',
                ]);
            }
        }

        // 5. Create 10 Additional COS Applicants
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => "cos_applicant_{$i}@demo.com"],
                [
                    'name' => "COS Applicant {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            PersonalInformation::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => "COS{$i}",
                    'surname' => "Applicant",
                    'middle_name' => "Demo",
                    'name_extension' => '',
                    'date_of_birth' => '1995-01-01',
                    'sex' => ($i % 2 == 0) ? 'Male' : 'Female',
                    'civil_status' => 'Single',
                    'mobile_no' => '0998765432' . $i,
                    'email_address' => "cos_applicant_{$i}@demo.com",
                ]
            );

            Applications::create([
                'user_id' => $user->id,
                'vacancy_id' => $cosJob->vacancy_id,
                'status' => 'Pending',
            ]);

            foreach ($cosDocs as $type => $status) {
                // Reset status to Pending for new applicants unless we want pre-verified ones
                $docStatus = 'Pending';
                
                UploadedDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $type,
                    'original_name' => "demo_{$type}_{$i}.pdf",
                    'stored_name' => "demo_{$type}_{$i}.pdf",
                    'storage_path' => "demo/path/{$type}.pdf", // Reusing the same dummy file path
                    'mime_type' => 'application/pdf',
                    'file_size_8b' => 1024,
                    'status' => $docStatus,
                    'remarks' => '',
                ]);
            }
        }
    }
}
