<?php

namespace Tests\Feature;

use App\Http\Controllers\JobVacancyController;
use App\Models\CivilServiceEligibility;
use App\Models\EducationalBackground;
use App\Models\JobVacancy;
use App\Models\LearningAndDevelopment;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualificationGatePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_higher_degree_substitutes_level_but_not_specific_field_automatically(): void
    {
        $user = User::factory()->create();
        $this->seedEducation($user, null, [[
            'school' => 'Graduate School',
            'basic' => 'Master of Education',
            'earned' => 'Graduate',
            'year_graduated' => '2024',
        ]]);

        $controller = app(JobVacancyController::class);
        $genericBachelor = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy("Bachelor's Degree")
        );

        $this->assertTrue((bool) ($genericBachelor['checks']['education']['met'] ?? false));

        $specificBachelor = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy("Bachelor's Degree in Statistics or related field")
        );

        $this->assertFalse((bool) ($specificBachelor['checks']['education']['met'] ?? true));

        $this->seedEducation($user, null, [[
            'school' => 'Graduate School',
            'basic' => 'Master of Science in Statistics',
            'earned' => 'Graduate',
            'year_graduated' => '2024',
        ]]);

        $specificBachelorRelated = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy("Bachelor's Degree in Statistics or related field")
        );

        $this->assertTrue((bool) ($specificBachelorRelated['checks']['education']['met'] ?? false));
    }

    public function test_specific_masteral_and_doctorate_requirements_enforce_level_and_field(): void
    {
        $user = User::factory()->create();
        $controller = app(JobVacancyController::class);

        $this->seedEducation($user, null, [[
            'school' => 'Graduate School',
            'basic' => 'Master of Arts in Education',
            'earned' => 'Graduate',
            'year_graduated' => '2024',
        ]]);

        $masteralSpecificFail = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Masteral Degree in Public Administration')
        );
        $this->assertFalse((bool) ($masteralSpecificFail['checks']['education']['met'] ?? true));

        $this->seedEducation($user, null, [[
            'school' => 'Graduate School',
            'basic' => 'Master in Public Administration',
            'earned' => 'Graduate',
            'year_graduated' => '2024',
        ]]);

        $masteralSpecificPass = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Masteral Degree in Public Administration')
        );
        $this->assertTrue((bool) ($masteralSpecificPass['checks']['education']['met'] ?? false));

        $doctorateGenericFail = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Doctorate Degree')
        );
        $this->assertFalse((bool) ($doctorateGenericFail['checks']['education']['met'] ?? true));

        $this->seedEducation($user, null, [[
            'school' => 'Graduate School',
            'basic' => 'Doctor of Education',
            'earned' => 'Graduate',
            'year_graduated' => '2025',
        ]]);

        $doctorateGenericPass = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Doctorate Degree')
        );
        $this->assertTrue((bool) ($doctorateGenericPass['checks']['education']['met'] ?? false));

        $doctorateSpecificFail = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Doctorate Degree in Public Administration')
        );
        $this->assertFalse((bool) ($doctorateSpecificFail['checks']['education']['met'] ?? true));
    }

    public function test_two_year_college_requirement_uses_explicit_undergrad_level(): void
    {
        $user = User::factory()->create();
        $controller = app(JobVacancyController::class);
        $requirement = 'Completion of 2 years of studies in college';

        $this->seedEducation($user, [[
            'school' => 'State University',
            'basic' => 'BS Information Technology',
            'from' => '2018-06-01',
            'to' => '2020-03-01',
            'earned' => 'Second year',
            'year_graduated' => '',
        ]], null);

        $undergradPass = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy($requirement)
        );
        $this->assertTrue((bool) ($undergradPass['checks']['education']['met'] ?? false));
        $this->assertSame(
            'college_undergrad_or_two_years',
            $undergradPass['checks']['education']['rule_code'] ?? null
        );

        $this->seedEducation($user, null, null, [
            'jhs_school' => 'Sample SHS',
            'jhs_basic' => 'Senior High School',
            'jhs_earned' => 'Grade 12 Completed',
            'jhs_year_graduated' => '2020',
        ]);
        $highSchoolFail = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy($requirement)
        );
        $this->assertFalse((bool) ($highSchoolFail['checks']['education']['met'] ?? true));

        $this->seedEducation($user, [[
            'school' => 'State University',
            'basic' => 'Bachelor of Public Administration',
            'from' => '2018-06-01',
            'to' => '2022-03-01',
            'earned' => 'Graduate',
            'year_graduated' => '2022',
        ]], null);
        $bachelorPass = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy($requirement)
        );
        $this->assertTrue((bool) ($bachelorPass['checks']['education']['met'] ?? false));
    }

    public function test_ra_1080_is_not_auto_satisfied_by_csc_professional(): void
    {
        $user = User::factory()->create();
        $controller = app(JobVacancyController::class);

        CivilServiceEligibility::query()->create([
            'user_id' => $user->id,
            'cs_eligibility_career' => 'CSC Professional/Second Level Eligibility',
            'cs_eligibility_rating' => '80',
            'cs_eligibility_date' => '2020-01-01',
            'cs_eligibility_place' => 'Baguio',
            'cs_eligibility_license' => null,
            'cs_eligibility_validity' => null,
        ]);

        $ra1080Vacancy = $this->makeVacancy('None Required', '[{"name":"RA 1080","legalBasis":"","level":""}]');
        $withCscProfessional = $controller->evaluateQualificationGateForApplicant($user->id, $ra1080Vacancy);

        $this->assertFalse((bool) ($withCscProfessional['checks']['eligibility']['met'] ?? true));

        CivilServiceEligibility::query()->where('user_id', $user->id)->delete();
        CivilServiceEligibility::query()->create([
            'user_id' => $user->id,
            'cs_eligibility_career' => 'RA 1080',
            'cs_eligibility_rating' => '80',
            'cs_eligibility_date' => '2020-01-01',
            'cs_eligibility_place' => 'Baguio',
            'cs_eligibility_license' => null,
            'cs_eligibility_validity' => null,
        ]);

        $withRa1080 = $controller->evaluateQualificationGateForApplicant($user->id, $ra1080Vacancy);
        $this->assertTrue((bool) ($withRa1080['checks']['eligibility']['met'] ?? false));
    }

    public function test_unmapped_related_field_does_not_auto_pass(): void
    {
        $user = User::factory()->create();
        $controller = app(JobVacancyController::class);

        $this->seedEducation($user, [[
            'school' => 'State University',
            'basic' => 'Bachelor of Science in Biology',
            'from' => '2018-06-01',
            'to' => '2022-03-01',
            'earned' => 'Graduate',
            'year_graduated' => '2022',
        ]], null);

        $vacancy = $this->makeVacancy("Bachelor's Degree in Astrobiology or related field");
        $biologyFail = $controller->evaluateQualificationGateForApplicant($user->id, $vacancy);
        $this->assertFalse((bool) ($biologyFail['checks']['education']['met'] ?? true));

        $this->seedEducation($user, [[
            'school' => 'State University',
            'basic' => 'Bachelor of Science in Astrobiology',
            'from' => '2018-06-01',
            'to' => '2022-03-01',
            'earned' => 'Graduate',
            'year_graduated' => '2022',
        ]], null);

        $astrobiologyPass = $controller->evaluateQualificationGateForApplicant($user->id, $vacancy);
        $this->assertTrue((bool) ($astrobiologyPass['checks']['education']['met'] ?? false));
    }

    public function test_related_field_mapping_can_be_extended_via_config_without_evaluator_changes(): void
    {
        $originalGroups = config('education_field_mapping.field_groups');
        $originalRelated = config('education_field_mapping.related_groups');

        try {
            config()->set('education_field_mapping.field_groups.astrobiology', ['astrobiology']);
            config()->set('education_field_mapping.field_groups.biology', ['biology']);
            config()->set('education_field_mapping.related_groups.astrobiology', ['biology']);

            $user = User::factory()->create();
            $controller = app(JobVacancyController::class);

            $this->seedEducation($user, [[
                'school' => 'State University',
                'basic' => 'Bachelor of Science in Biology',
                'from' => '2018-06-01',
                'to' => '2022-03-01',
                'earned' => 'Graduate',
                'year_graduated' => '2022',
            ]], null);

            $vacancy = $this->makeVacancy("Bachelor's Degree in Astrobiology or related field");
            $result = $controller->evaluateQualificationGateForApplicant($user->id, $vacancy);

            $this->assertTrue((bool) ($result['checks']['education']['met'] ?? false));
        } finally {
            config()->set('education_field_mapping.field_groups', $originalGroups);
            config()->set('education_field_mapping.related_groups', $originalRelated);
        }
    }

    public function test_juris_doctor_is_law_track_only_by_default_policy(): void
    {
        $user = User::factory()->create();
        $controller = app(JobVacancyController::class);

        $this->seedEducation($user, [[
            'school' => 'Law School',
            'basic' => 'Juris Doctor',
            'from' => '2017-06-01',
            'to' => '2021-03-01',
            'earned' => 'Graduate',
            'year_graduated' => '2021',
        ]], null);

        $doctorateVacancy = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Doctorate Degree')
        );
        $this->assertFalse((bool) ($doctorateVacancy['checks']['education']['met'] ?? true));

        $lawVacancy = $controller->evaluateQualificationGateForApplicant(
            $user->id,
            $this->makeVacancy('Bachelor of Laws')
        );
        $this->assertTrue((bool) ($lawVacancy['checks']['education']['met'] ?? false));
    }

    private function makeVacancy(
        string $educationRequirement,
        string $eligibilityRequirement = 'None Required',
        string $trainingRequirement = 'None Required',
        string $experienceRequirement = 'None Required'
    ): JobVacancy {
        return new JobVacancy([
            'vacancy_id' => 'SIM-001',
            'position_title' => 'Simulation Position',
            'vacancy_type' => 'Plantilla',
            'qualification_education' => $educationRequirement,
            'qualification_training' => $trainingRequirement,
            'qualification_experience' => $experienceRequirement,
            'qualification_eligibility' => $eligibilityRequirement,
        ]);
    }

    private function seedEducation(User $user, ?array $college, ?array $grad, array $baseOverrides = []): void
    {
        EducationalBackground::query()->where('user_id', $user->id)->delete();
        LearningAndDevelopment::query()->where('user_id', $user->id)->delete();
        WorkExperience::query()->where('user_id', $user->id)->delete();
        CivilServiceEligibility::query()->where('user_id', $user->id)->delete();

        $payload = array_merge([
            'user_id' => $user->id,
            'elem_school' => null,
            'elem_from' => null,
            'elem_to' => null,
            'elem_academic_honors' => null,
            'elem_basic' => null,
            'elem_earned' => null,
            'elem_year_graduated' => null,
            'jhs_from' => null,
            'jhs_to' => null,
            'jhs_school' => null,
            'jhs_academic_honors' => null,
            'jhs_basic' => null,
            'jhs_earned' => null,
            'jhs_year_graduated' => null,
            'shs_from' => null,
            'shs_to' => null,
            'shs_school' => null,
            'shs_academic_honors' => null,
            'shs_basic' => null,
            'shs_earned' => null,
            'shs_year_graduated' => null,
            'vocational' => null,
            'college' => $college,
            'grad' => $grad,
        ], $baseOverrides);

        EducationalBackground::query()->create($payload);
    }
}
