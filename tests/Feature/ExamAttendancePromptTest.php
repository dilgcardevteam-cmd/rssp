<?php

namespace Tests\Feature;

use App\Models\Applications;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAttendancePromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_prompt_allows_existing_response_to_be_overridden(): void
    {
        [$user, $vacancy, $application] = $this->createQualifiedAttendanceContext([
            'exam_attendance_status' => 'will_attend',
            'exam_attendance_responded_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('exam.attendance.prompt', ['vacancy_id' => $vacancy->vacancy_id]));

        $response->assertOk();
        $response->assertSeeText('Your exam attendance response has already been recorded. You can still override your attendance below.');
        $response->assertSeeText('Override Attendance Response');
        $response->assertSeeText('Will Attend');
    }

    public function test_submitting_attendance_again_updates_existing_response(): void
    {
        [$user, $vacancy, $application] = $this->createQualifiedAttendanceContext([
            'exam_attendance_status' => 'will_attend',
            'exam_attendance_responded_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('exam.attendance.respond', ['vacancy_id' => $vacancy->vacancy_id]), [
                'attendance_status' => 'will_not_attend',
                'attendance_remark' => 'Family emergency',
            ]);

        $response->assertRedirect(route('application_status', ['user' => $user->id, 'vacancy' => $vacancy->vacancy_id]));
        $response->assertSessionHas('success', 'Your exam attendance response has been updated to Will Not Attend.');

        $application->refresh();
        $this->assertSame('will_not_attend', $application->exam_attendance_status);
        $this->assertSame('Family emergency', $application->exam_attendance_remark);
        $this->assertNotNull($application->exam_attendance_responded_at);
    }

    private function createQualifiedAttendanceContext(array $applicationOverrides = []): array
    {
        $user = User::factory()->create();

        $vacancy = JobVacancy::create([
            'vacancy_id' => 'ATT-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'position_title' => 'Administrative Officer',
            'vacancy_type' => 'Plantilla',
            'monthly_salary' => 25000,
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
            'place_of_assignment' => 'Baguio City Office',
        ]);

        ExamDetail::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'place' => 'Regional Office',
            'date' => now()->addDays(7)->toDateString(),
            'time' => '09:00',
        ]);

        $application = Applications::create(array_merge([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Qualified',
        ], $applicationOverrides));

        return [$user, $vacancy, $application];
    }
}
