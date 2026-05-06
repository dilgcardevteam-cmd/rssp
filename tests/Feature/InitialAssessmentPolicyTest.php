<?php

namespace Tests\Feature;

use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InitialAssessmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_assessment_generic_bachelor_rejects_high_school_and_accepts_masteral(): void
    {
        $user = User::factory()->create();
        $vacancy = $this->makeVacancy("Bachelor's Degree");

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Senior High School Graduate',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'education_aligned' => false,
            ]);

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Master in Public Administration',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
                'requires_pqe' => false,
            ]);
    }

    public function test_initial_assessment_generic_bachelor_accepts_custom_course_name_without_explicit_degree_keyword(): void
    {
        $user = User::factory()->create();
        $vacancy = $this->makeVacancy("Bachelor's Degree");

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Community Development',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
            ]);

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'First year college undergraduate',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'education_aligned' => false,
            ]);
    }

    public function test_initial_assessment_specific_field_keeps_level_substitution_but_requires_field_match(): void
    {
        $user = User::factory()->create();
        $vacancy = $this->makeVacancy("Bachelor's Degree in Statistics or related field");

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Master of Arts in Education',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'education_aligned' => false,
            ]);

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Master of Science in Statistics',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
            ]);
    }

    public function test_initial_assessment_or_based_education_requirement_handles_grade12_alternative_with_level_substitution(): void
    {
        $user = User::factory()->create();
        $vacancy = $this->makeVacancy(
            'Completion of 2 years of studies in college OR Completion of Grade 12/Senior High School (starting 2016)'
        );

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'First year college undergraduate',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
            ]);

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Grade 12 Senior High School Graduate',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
            ]);

        $strictTwoYearsVacancy = $this->makeVacancy('Completion of 2 years of studies in college');

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $strictTwoYearsVacancy->vacancy_id]), [
                'degree' => 'First year college undergraduate',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'education_aligned' => false,
            ]);
    }

    public function test_initial_assessment_juris_doctor_is_law_track_only_by_default_policy(): void
    {
        $user = User::factory()->create();
        $doctorateVacancy = $this->makeVacancy('Doctorate Degree');

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $doctorateVacancy->vacancy_id]), [
                'degree' => 'Juris Doctor',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'education_aligned' => false,
            ]);

        $lawVacancy = $this->makeVacancy('Bachelor of Laws');

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $lawVacancy->vacancy_id]), [
                'degree' => 'Juris Doctor',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'education_aligned' => true,
            ]);
    }

    public function test_initial_assessment_plantilla_requires_explicit_pqe_answer_before_completion(): void
    {
        $user = User::factory()->create();
        $vacancy = $this->makeVacancy("Bachelor's Degree", 'None Required', 'Plantilla');

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Bachelor of Science in Public Administration',
                'eligibility' => 'CSC Professional Eligibility',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'requires_pqe' => true,
            ]);

        $this->actingAs($user)
            ->postJson(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]), [
                'degree' => 'Bachelor of Science in Public Administration',
                'eligibility' => 'CSC Professional Eligibility',
                'has_pqe' => false,
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'requires_pqe' => false,
            ])
            ->assertSessionHas('initial_assessment_answers.' . $vacancy->vacancy_id . '.has_pqe', false);
    }

    private function makeVacancy(
        string $educationRequirement,
        string $eligibilityRequirement = 'None Required',
        string $vacancyType = 'COS'
    ): JobVacancy {
        return JobVacancy::query()->create([
            'vacancy_id' => 'IA',
            'position_title' => 'Simulation Position',
            'vacancy_type' => $vacancyType,
            'pcn_no' => null,
            'plantilla_item_no' => null,
            'monthly_salary' => 50000,
            'salary_grade' => '11',
            'place_of_assignment' => 'Baguio City',
            'status' => 'OPEN',
            'closing_date' => Carbon::now()->addDays(7),
            'qualification_education' => $educationRequirement,
            'qualification_training' => 'None Required',
            'qualification_experience' => 'None Required',
            'qualification_eligibility' => $eligibilityRequirement,
            'competencies' => null,
            'expected_output' => null,
            'scope_of_work' => null,
            'duration_of_work' => null,
            'to_person' => 'HRMO',
            'to_position' => 'HR Officer',
            'to_office' => 'DILG-CAR',
            'to_office_address' => 'Baguio City',
            'last_modified_by' => 'Test Suite',
        ]);
    }
}
