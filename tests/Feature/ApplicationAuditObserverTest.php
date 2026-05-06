<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applications;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApplicationAuditObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_updates_create_audit_activity_log(): void
    {
        Queue::fake();

        $admin = Admin::create([
            'username' => 'audit_admin',
            'name' => 'Audit Admin',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'audit-admin@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $user = User::factory()->create();
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'AUDIT-001',
            'position_title' => 'Information Systems Analyst I',
            'vacancy_type' => 'COS',
            'monthly_salary' => 25000,
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
            'place_of_assignment' => 'Baguio',
        ]);

        $application = Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'is_valid' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $application->update([
            'status' => 'Compliance',
            'application_remarks' => 'Please update your document.',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Application critical fields changed.',
            'event' => 'audit_update',
        ]);

        $activity = DB::table('activity_log')
            ->where('description', 'Application critical fields changed.')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Compliance', (string) $activity->properties);
    }
}

