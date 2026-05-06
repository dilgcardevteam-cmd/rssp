<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applications;
use App\Models\JobVacancy;
use App\Models\User;
use App\Models\UploadedDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create necessary data
        $this->admin = Admin::create([
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'name' => 'Test Admin',
            'role' => 'admin',
            'email' => 'admin@test.com',
            'office' => 'Test Office',
            'designation' => 'Test Designation',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_mark_missing_document_as_needs_revision_and_update_status()
    {
        // 1. Create User and Job Vacancy
        $user = User::factory()->create();
        $vacancy = JobVacancy::create([
            'position_title' => 'Test Position',
            'vacancy_type' => 'Plantilla',
            'monthly_salary' => 10000,
            'place_of_assignment' => 'Test Place',
            'closing_date' => now()->addDays(10),
            'status' => 'OPEN',
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => 'None',
            'qualification_eligibility' => 'None',
            'to_person' => 'Test Person',
            'to_position' => 'Test Position',
            'to_office' => 'Test Office',
            'to_office_address' => 'Test Address',
        ]);

        // 2. Create Application with NO documents
        $application = Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'is_valid' => true,
        ]);

        // 3. Admin marks 'pds' as 'Needs Revision'
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.applicant_status.update_document', [
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->vacancy_id
            ]), [
                'document_type' => 'signed_pds',
                'status' => 'Needs Revision',
                'remarks' => 'Please upload PDS',
            ]);
        $response->assertStatus(200);

        // 4. Admin notifies applicant
        $responseNotify = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.applicant_status.notify', [
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->vacancy_id
            ]));
        $responseNotify->assertStatus(200);

        // 5. Assert Status
        $application->refresh();
        $this->assertEquals('Compliance', $application->status);
    }

    public function test_admin_can_mark_missing_application_letter_as_needs_revision()
    {
        // 1. Create User and Job Vacancy
        $user = User::factory()->create();
        $vacancy = JobVacancy::create([
            'position_title' => 'Test Position 2',
            'vacancy_type' => 'COS',
            'monthly_salary' => 10000,
            'place_of_assignment' => 'Test Place',
            'closing_date' => now()->addDays(10),
            'status' => 'OPEN',
            'qualification_education' => 'Any',
            'qualification_training' => 'None',
            'qualification_experience' => 'None',
            'qualification_eligibility' => 'None',
            'to_person' => 'Test Person',
            'to_position' => 'Test Position',
            'to_office' => 'Test Office',
            'to_office_address' => 'Test Address',
        ]);

        // 2. Create Application with NO documents
        $application = Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'is_valid' => true,
        ]);

        // 3. Admin marks 'application_letter' as 'Needs Revision'
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.applicant_status.update_document', [
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->vacancy_id
            ]), [
                'document_type' => 'application_letter',
                'status' => 'Needs Revision',
                'remarks' => 'Missing letter',
            ]);
        $response->assertStatus(200);

        // 4. Admin notifies applicant
        $responseNotify = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.applicant_status.notify', [
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->vacancy_id
            ]));
        $responseNotify->assertStatus(200);

        // 5. Assert Status
        $application->refresh();
        $this->assertEquals('Compliance', $application->status);
    }

    public function test_user_dashboard_sees_live_deadline_update()
    {
        // 1. Create User and Vacancy
        $user = User::factory()->create();
        $vacancy = JobVacancy::create([
            'position_title' => 'Test Position 3',
            'vacancy_type' => 'COS',
            'monthly_salary' => 10000,
            'place_of_assignment' => 'Tp',
            'closing_date' => now()->addDays(10),
            'status' => 'OPEN',
            'qualification_education' => 'A',
            'qualification_training' => 'N',
            'qualification_experience' => 'N',
            'qualification_eligibility' => 'N',
            'to_person' => 'TP',
            'to_position' => 'TP',
            'to_office' => 'TO',
            'to_office_address' => 'TA',
        ]);

        $application = Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'deadline_date' => '2025-01-01',
            'deadline_time' => '12:00:00',
        ]);

        // 2. Admin notifies (creating a snapshot with 2025-01-01)
        // This simulates the admin setting a deadline AND notifying
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.applicant_status.notify', [
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->vacancy_id
            ]), [
                'deadline_date' => '2025-01-01',
                'deadline_time' => '12:00',
            ]);

        // Assert deadline was saved
        $application->refresh();
        $this->assertEquals('2025-01-01', $application->deadline_date);
        $this->assertEquals('12:00', $application->deadline_time);

        // 3. User views dashboard -> should see 2025-01-01
        $this->withoutMiddleware(\App\Http\Middleware\BlockIfAdmin::class);
        $response = $this->actingAs($user)
            ->get(route('application_status', ['user' => $user->id, 'vacancy' => $vacancy->vacancy_id]));
        $response->assertSee('January 01, 2025');

        // 4. Admin updates deadline WITHOUT notifying
        $application->update([
            'deadline_date' => '2025-12-31',
        ]);

        // 5. User views dashboard -> should see 2025-12-31 (Live Data), NOT the snapshot data
        $response = $this->actingAs($user)
            ->get(route('application_status', ['user' => $user->id, 'vacancy' => $vacancy->vacancy_id]));

        $response->assertSee('December 31, 2025');
    }
}
