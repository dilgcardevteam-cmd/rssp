<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\ExamDetail;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyApplicantMail;

class ExamNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_and_notify_triggers_job()
    {
        // 1. Setup Data
        Queue::fake();
        Mail::fake();

        // Create Admin User for auth
        $admin = Admin::create([
            'username' => 'test_admin',
            'name' => 'Test Admin',
            'office' => 'IT',
            'designation' => 'Developer',
            'email' => 'admin@dilg.gov.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Create Vacancy
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'TEST-001',
            'position_title' => 'Test Position',
            'vacancy_type' => 'COS',
            'monthly_salary' => 35000,
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

        // Create Applicant
        $user = User::factory()->create();
        Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Qualified',
        ]);

        // 2. Simulate Request
        $response = $this->actingAs($admin, 'admin') // Assuming 'admin' guard
            ->postJson("/admin/exam_management/{$vacancy->vacancy_id}/details/save", [
                'place' => 'Test Venue',
                'date' => '2025-12-31',
                'time' => '09:00',
                'time_end' => '10:00',
                'duration' => 60,
                'max_violations' => 3,
                'notify' => 1 // Critical flag
            ]);

        // 3. Assertions
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'notified' => true]);

        // saveExamDetails(notify=1) sends exam schedule mails immediately.
        Mail::assertSent(NotifyApplicantMail::class, function ($mail) use ($user, $vacancy) {
            return $mail->user_id === $user->id
                && $mail->vacancy_id === $vacancy->vacancy_id
                && $mail->senderName === 'Test Admin'
                && str_contains($mail->render(), 'Sent by:')
                && str_contains($mail->render(), 'Test Admin');
        });

        // Check Exam Detail Updated
        $this->assertDatabaseHas('exam_details', [
            'vacancy_id' => $vacancy->vacancy_id,
            'place' => 'Test Venue'
        ]);
    }

    public function test_save_and_notify_creates_in_app_notification_for_attendance_prompt(): void
    {
        Mail::fake();

        $admin = Admin::create([
            'username' => 'notify_admin',
            'name' => 'Notify Admin',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'notify-admin@dilg.gov.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $vacancy = JobVacancy::create([
            'vacancy_id' => 'TEST-ATTEND-001',
            'position_title' => 'Project Development Officer',
            'vacancy_type' => 'Plantilla',
            'monthly_salary' => 42000,
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

        $user = User::factory()->create(['email' => 'attendance-applicant@example.com']);
        Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Qualified',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/admin/exam_management/{$vacancy->vacancy_id}/details/save", [
                'place' => 'Regional Office',
                'date' => now()->addDays(3)->toDateString(),
                'time' => '09:00',
                'time_end' => '10:00',
                'duration' => 60,
                'max_violations' => 3,
                'message' => 'Please confirm attendance.',
                'notify' => 1,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true, 'notified' => true]);

        $notification = Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('data->type', 'exam_attendance_prompt')
            ->latest()
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('Exam Attendance Confirmation', $notification->data['title'] ?? null);
        $this->assertSame(
            route('exam.attendance.prompt', ['vacancy_id' => $vacancy->vacancy_id], false),
            $notification->data['action_url'] ?? null
        );

        Mail::assertSent(NotifyApplicantMail::class, function ($mail) use ($user, $vacancy) {
            return $mail->user_id === $user->id
                && $mail->vacancy_id === $vacancy->vacancy_id
                && $mail->senderName === 'Notify Admin'
                && str_contains($mail->render(), 'Sent by:')
                && str_contains($mail->render(), 'Notify Admin');
        });
    }

    public function test_sending_exam_link_creates_clickable_in_app_notification_for_applicant(): void
    {
        Queue::fake();

        $admin = Admin::create([
            'username' => 'link_notify_admin',
            'name' => 'Link Notify Admin',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'link-notify-admin@dilg.gov.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $vacancy = JobVacancy::create([
            'vacancy_id' => 'TEST-LINK-NOTIF-001',
            'position_title' => 'Administrative Officer',
            'vacancy_type' => 'Plantilla',
            'monthly_salary' => 42000,
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

        ExamDetail::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'place' => 'Regional Office',
            'date' => now()->addDays(3)->toDateString(),
            'time' => '09:00',
            'time_end' => '10:00',
            'duration' => 60,
            'details_saved' => true,
        ]);

        $user = User::factory()->create(['email' => 'exam-link-applicant@example.com']);
        $application = Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Qualified',
            'exam_attendance_status' => 'will_attend',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.exam.notify_selected', ['vacancy_id' => $vacancy->vacancy_id]), [
                'user_ids' => [$user->id],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $application->refresh();
        $this->assertNotNull($application->exam_token);

        $notification = Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('data->type', 'exam_link_available')
            ->latest()
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('Exam Link Available', $notification->data['title'] ?? null);
        $this->assertSame(
            route('user.exam_lobby', ['vacancy_id' => $vacancy->vacancy_id, 'token' => $application->exam_token], false),
            $notification->data['action_url'] ?? null
        );
    }

    public function test_exam_sched_link_view_displays_sender_name(): void
    {
        $html = view('emails.exam_sched_link', [
            'user' => (object) ['name' => 'Applicant Name'],
            'vacancy' => (object) ['position_title' => 'Administrative Officer'],
            'exam' => (object) ['date' => now()->toDateString(), 'time' => '09:00', 'place' => 'Regional Office'],
            'join_link' => 'https://example.com/exam-link',
            'link_expires_at' => now()->addHour(),
            'senderName' => 'Link Notify Admin',
        ])->render();

        $this->assertStringContainsString('Sent by:', $html);
        $this->assertStringContainsString('Link Notify Admin', $html);
    }
}
