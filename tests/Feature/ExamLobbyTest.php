<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\PersonalInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendExamNotification;
use Carbon\Carbon;
use App\Models\ExamTabViolation;
use ReflectionClass;

class ExamLobbyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_fetch_lobby_data()
    {
        // Setup
        $admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin', // Correct role
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director'
        ]);

        $vacancy = JobVacancy::factory()->create();
        $applicant = User::factory()->create();

        $personalInfo = PersonalInformation::factory()->create(['user_id' => $applicant->id]);

        $application = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
            'status' => 'qualified'
        ]);

        // Act
        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.exam.lobby_data', ['vacancy_id' => $vacancy->vacancy_id]));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'participants'])
            ->assertJsonPath('success', true);

        // We might not see the name directly if the factory setup is minimal, 
        // but let's check if we get at least one participant.
        $this->assertCount(1, $response->json('participants'));
    }

    public function test_admin_can_send_notifications_to_selected_applicants()
    {
        Queue::fake();

        // Setup
        $admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest2', // Unique username
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director'
        ]);

        $vacancy = JobVacancy::factory()->create();
        $detail = ExamDetail::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'details_saved' => true
        ]);

        $applicant1 = User::factory()->create();
        $app1 = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant1->id,
            'status' => 'qualified',
            'exam_attendance_status' => 'will_attend',
        ]);

        $applicant2 = User::factory()->create();
        $app2 = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant2->id,
            'status' => 'qualified',
            'exam_attendance_status' => 'will_not_attend',
        ]);

        // Act - Select applicant 1 only
        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.exam.notify_selected', ['vacancy_id' => $vacancy->vacancy_id]), [
                'user_ids' => [$applicant1->id]
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check Job Pushed for Applicant 1
        Queue::assertPushed(SendExamNotification::class, function ($job) use ($applicant1) {
            $reflection = new ReflectionClass($job);
            $property = $reflection->getProperty('userId');
            $property->setAccessible(true);
            return $property->getValue($job) === $applicant1->id;
        });

        // Check Job NOT Pushed for Applicant 2
        Queue::assertNotPushed(SendExamNotification::class, function ($job) use ($applicant2) {
            $reflection = new ReflectionClass($job);
            $property = $reflection->getProperty('userId');
            $property->setAccessible(true);
            return $property->getValue($job) === $applicant2->id;
        });

        // Check DB for Token Generation
        $this->assertNotNull($app1->fresh()->exam_token, 'Exam token should be generated for applicant 1');
        $this->assertNull($app2->fresh()->exam_token, 'Exam token should NOT be generated for applicant 2');
    }

    public function test_admin_can_resume_threshold_auto_submitted_exam_with_saved_progress(): void
    {
        Carbon::setTestNow('2026-03-19 10:00:00');

        $admin = Admin::create([
            'name' => 'Resume Admin',
            'username' => 'resume-admin',
            'email' => 'resume-admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director',
        ]);

        $vacancy = JobVacancy::factory()->create();
        $applicant = User::factory()->create();

        ExamDetail::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'max_violations' => 3,
        ]);

        $application = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
            'status' => 'submitted',
            'answers' => ['101' => 'A', '102' => 'Essay draft'],
            'scores' => ['101' => 1, '102' => null],
            'exam_started_at' => Carbon::now()->subMinutes(15),
            'exam_submitted_at' => Carbon::now()->subMinute(),
            'exam_end_time' => Carbon::now()->addMinutes(14),
            'tab_violations' => 3,
        ]);

        ExamTabViolation::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
            'started_at' => Carbon::now()->subMinutes(3),
            'ended_at' => Carbon::now()->subMinutes(3)->addSeconds(4),
            'duration_seconds' => 4,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.exam.resume', [
                'vacancy_id' => $vacancy->vacancy_id,
                'user_id' => $applicant->id,
            ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'in-progress',
                'remaining_seconds' => 900,
                'remaining_label' => '15:00',
            ]);

        $application = Applications::where('vacancy_id', $vacancy->vacancy_id)
            ->where('user_id', $applicant->id)
            ->firstOrFail();

        $this->assertSame('in-progress', $application->status);
        $this->assertNull($application->exam_submitted_at);
        $this->assertSame(['101' => 'A', '102' => 'Essay draft'], $application->answers);
        $this->assertSame(['101' => 1, '102' => null], $application->scores);
        $this->assertSame(0, (int) $application->tab_violations);
        $this->assertNull($application->last_tab_violation_at);
        $this->assertSame('2026-03-19 10:15:00', Carbon::parse((string) $application->exam_end_time)->format('Y-m-d H:i:s'));
        $this->assertDatabaseMissing('exam_tab_violations', [
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
        ]);
    }

    public function test_admin_cannot_resume_regular_submitted_exam_without_threshold_auto_submit(): void
    {
        Carbon::setTestNow('2026-03-19 10:00:00');

        $admin = Admin::create([
            'name' => 'Resume Guard Admin',
            'username' => 'resume-guard-admin',
            'email' => 'resume-guard-admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director',
        ]);

        $vacancy = JobVacancy::factory()->create();
        $applicant = User::factory()->create();

        ExamDetail::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'max_violations' => 3,
        ]);

        Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
            'status' => 'submitted',
            'answers' => ['201' => 'B'],
            'scores' => ['201' => 1],
            'exam_started_at' => Carbon::now()->subMinutes(15),
            'exam_submitted_at' => Carbon::now()->subMinutes(2),
            'exam_end_time' => Carbon::now()->addMinutes(10),
            'tab_violations' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.exam.resume', [
                'vacancy_id' => $vacancy->vacancy_id,
                'user_id' => $applicant->id,
            ]));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Only attempts auto-submitted by the tab-switch threshold can be resumed.',
            ]);

        $application = Applications::where('vacancy_id', $vacancy->vacancy_id)
            ->where('user_id', $applicant->id)
            ->firstOrFail();

        $this->assertSame('submitted', $application->status);
        $this->assertNotNull($application->exam_submitted_at);
    }
}
