<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applications;
use App\Models\EducationalBackground;
use App\Models\JobVacancy;
use App\Models\Notification;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_snapshot_includes_status_remarks_and_deadline(): void
    {
        Mail::fake();
        $admin = Admin::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['email' => 'applicant@example.com']);
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'ISAI-028',
            'position_title' => 'Information Systems Analyst I',
            'vacancy_type' => 'COS',
            'monthly_salary' => 27000,
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => '1 year',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HR',
            'to_office' => 'DILG',
            'to_office_address' => 'Baguio',
            'place_of_assignment' => 'Baguio',
        ]);

        $application = Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Compliance',
            'deadline_date' => now()->addDays(3)->format('Y-m-d'),
            'deadline_time' => '17:00',
            'file_status' => 'Needs Revision',
            'file_remarks' => 'Fix application letter.',
        ]);

        UploadedDocument::create([
            'user_id' => $user->id,
            'document_type' => 'signed_pds',
            'original_name' => 'pds.pdf',
            'stored_name' => 'pds.pdf',
            'storage_path' => 'uploads/pds-files/pds.pdf',
            'mime_type' => 'application/pdf',
            'file_size_8b' => 100,
            'status' => 'Needs Revision',
            'remarks' => 'Update signature.',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post(route('admin.applicant_status.notify', [$user->id, $vacancy->vacancy_id]));
        $response->assertOk();

        $notification = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->where('data->type', 'application_overview')
            ->first();

        $this->assertNotNull($notification);
        $documents = collect($notification->data['documents'] ?? []);
        $signedPds = $documents->firstWhere('id', 'signed_pds');
        $this->assertSame('Needs Revision', $signedPds['status'] ?? null);
        $this->assertSame('Update signature.', $signedPds['remarks'] ?? null);
        $this->assertSame($application->deadline_date, $notification->data['deadline_date'] ?? null);
        $this->assertSame($application->deadline_time, $notification->data['deadline_time'] ?? null);
    }

    public function test_qualification_standards_recalculate_on_admin_update(): void
    {
        $admin = Admin::create([
            'username' => 'admin2',
            'name' => 'Admin Two',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'VAC-QUAL-1',
            'position_title' => 'Analyst',
            'vacancy_type' => 'COS',
            'monthly_salary' => 20000,
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'qualification_education' => 'Bachelor',
            'qualification_experience' => '1 year',
            'qualification_training' => 'None',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HR',
            'to_office' => 'DILG',
            'to_office_address' => 'Baguio',
            'place_of_assignment' => 'Baguio',
        ]);

        Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Pending',
        ]);

        EducationalBackground::create([
            'user_id' => $user->id,
            'college' => [['school' => 'Test University', 'basic' => 'BS IT']],
        ]);

        WorkExperience::create([
            'user_id' => $user->id,
            'work_exp_from' => '2022-01-01',
            'work_exp_to' => '2023-02-01',
            'work_exp_position' => 'Developer',
            'work_exp_department' => 'IT',
            'work_exp_salary' => '20000',
            'work_exp_grade' => '1',
            'work_exp_status' => 'Permanent',
            'work_exp_govt_service' => 'Yes',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post(route('admin.applicant_status.update', [$user->id, $vacancy->vacancy_id]), [
            'status' => 'Pending',
            'deadline_date' => now()->addDays(2)->format('Y-m-d'),
            'deadline_time' => '17:00',
            'document_statuses' => [],
            'document_remarks' => [],
        ]);

        $response->assertRedirect();
        $application = Applications::where('user_id', $user->id)
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();

        $this->assertSame('yes', $application->qs_education);
        $this->assertSame('yes', $application->qs_experience);
        $this->assertSame('Qualified', $application->qs_result);
    }
}
