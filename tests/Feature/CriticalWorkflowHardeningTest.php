<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applications;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CriticalWorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_requires_exact_email_and_password_case(): void
    {
        $user = User::factory()->create([
            'email' => 'case.user@example.com',
            'password' => Hash::make('CaseSensitive123!'),
        ]);

        $this->post(route('login'), [
            'email' => 'CASE.USER@EXAMPLE.COM',
            'password' => 'CaseSensitive123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), [
            'email' => 'case.user@example.com',
            'password' => 'CASESENSITIVE123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), [
            'email' => 'case.user@example.com',
            'password' => 'CaseSensitive123!',
        ])->assertRedirect(route('dashboard_user'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_requires_exact_email_and_password_case(): void
    {
        $admin = Admin::create([
            'username' => 'admin_case',
            'name' => 'Admin Case',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'admin.case@example.com',
            'password' => Hash::make('AdminCase123!'),
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'ADMIN.CASE@EXAMPLE.COM',
            'password' => 'AdminCase123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest('admin');

        $this->post(route('admin.login.submit'), [
            'email' => 'admin.case@example.com',
            'password' => 'ADMINCASE123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest('admin');

        $this->post(route('admin.login.submit'), [
            'email' => 'admin.case@example.com',
            'password' => 'AdminCase123!',
        ])->assertRedirect(route('dashboard_admin'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_viewer_cannot_access_exam_scoring_page(): void
    {
        $viewer = Admin::create([
            'username' => 'viewer_only',
            'name' => 'Viewer Only',
            'office' => 'HR',
            'designation' => 'Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('Viewer123!'),
            'role' => 'viewer',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-VIEWER-001');

        $this->actingAs($viewer, 'admin')
            ->get(route('admin.view_exam', [
                'vacancy_id' => $vacancy->vacancy_id,
                'user_id' => $user->id,
            ]))
            ->assertRedirect(route('viewer'));
    }

    public function test_compliance_ajax_lists_updated_status_records(): void
    {
        $admin = Admin::create([
            'username' => 'admin_filter',
            'name' => 'Admin Filter',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'admin.filter@example.com',
            'password' => Hash::make('AdminFilter123!'),
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $user = User::factory()->create(['name' => 'Updated Applicant']);
        $vacancy = $this->createVacancy('VAC-COMP-001');

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Updated',
            'is_valid' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/manage_applicants/compliance?vacancy_id=' . urlencode($vacancy->vacancy_id) . '&sort_order=latest', [
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertSee('Updated Applicant')
            ->assertSee('Updated');
    }

    private function createVacancy(string $vacancyId): JobVacancy
    {
        return JobVacancy::create([
            'vacancy_id' => $vacancyId,
            'position_title' => 'Information Systems Analyst I',
            'vacancy_type' => 'COS',
            'monthly_salary' => 25000,
            'place_of_assignment' => 'Baguio',
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => '1 year',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HRMO',
            'to_office' => 'DILG-CAR',
            'to_office_address' => 'Baguio City',
        ]);
    }
}
