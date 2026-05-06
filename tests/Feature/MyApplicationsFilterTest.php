<?php

namespace Tests\Feature;

use App\Models\Applications;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyApplicationsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_applications_page_renders_filter_controls(): void
    {
        $user = User::factory()->create();

        $vacancy = $this->createVacancy([
            'position_title' => 'Records Officer',
            'vacancy_type' => 'COS',
            'place_of_assignment' => 'Baguio City Office',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('my_applications'));

        $response->assertOk();
        $response->assertSee('Search by job title or vacancy ID', false);
        $response->assertSee('Place of Assignment', false);
        $response->assertSee('DILG-CAR Regional Office', false);
        $response->assertSee('Baguio City Office', false);
        $response->assertSee('COS / Plantilla', false);
        $response->assertSee('All Statuses', false);
    }

    public function test_my_applications_search_matches_job_title_and_vacancy_id(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $matchingVacancy = $this->createVacancy([
            'position_title' => 'Administrative Analyst',
            'vacancy_type' => 'COS',
            'place_of_assignment' => 'Baguio City Office',
        ]);

        $otherVacancy = $this->createVacancy([
            'position_title' => 'Finance Officer',
            'vacancy_type' => 'Plantilla',
            'place_of_assignment' => 'Abra Provincial Office',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $matchingVacancy->vacancy_id,
            'status' => 'Pending',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $otherVacancy->vacancy_id,
            'status' => 'Complete',
        ]);

        Applications::create([
            'user_id' => $otherUser->id,
            'vacancy_id' => $matchingVacancy->vacancy_id,
            'status' => 'Pending',
        ]);

        $this->actingAs($user);

        $titleSearchResponse = $this->get(route('my_applications.sort', ['search' => 'Analyst']));
        $titleSearchResponse->assertOk();
        $titleSearchResponse->assertSeeText('Administrative Analyst');
        $titleSearchResponse->assertDontSeeText('Finance Officer');

        $vacancySearchResponse = $this->get(route('my_applications.sort', ['search' => $matchingVacancy->vacancy_id]));
        $vacancySearchResponse->assertOk();
        $vacancySearchResponse->assertSeeText('Administrative Analyst');
        $vacancySearchResponse->assertSeeText($matchingVacancy->vacancy_id);
        $vacancySearchResponse->assertDontSeeText('Finance Officer');
    }

    public function test_my_applications_filters_by_place_type_and_status(): void
    {
        $user = User::factory()->create();

        $matchingVacancy = $this->createVacancy([
            'position_title' => 'Project Development Officer',
            'vacancy_type' => 'COS',
            'place_of_assignment' => 'Baguio City Office',
        ]);

        $wrongPlaceVacancy = $this->createVacancy([
            'position_title' => 'Project Evaluation Officer',
            'vacancy_type' => 'COS',
            'place_of_assignment' => 'Abra Provincial Office',
        ]);

        $wrongTypeVacancy = $this->createVacancy([
            'position_title' => 'Planning Officer',
            'vacancy_type' => 'Plantilla',
            'place_of_assignment' => 'Baguio City Office',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $matchingVacancy->vacancy_id,
            'status' => 'Pending',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $wrongPlaceVacancy->vacancy_id,
            'status' => 'Pending',
        ]);

        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $wrongTypeVacancy->vacancy_id,
            'status' => 'Complete',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('my_applications.sort', [
            'place' => 'Baguio City Office',
            'vacancy_type' => 'COS',
            'status' => 'Pending',
        ]));

        $response->assertOk();
        $response->assertSeeText('Project Development Officer');
        $response->assertDontSeeText('Project Evaluation Officer');
        $response->assertDontSeeText('Planning Officer');
    }

    private function createVacancy(array $overrides = []): JobVacancy
    {
        $vacancy = JobVacancy::factory()->create(array_merge([
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'monthly_salary' => 25000,
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => '1 year',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HR',
            'to_office' => 'DILG',
            'to_office_address' => 'Baguio City',
        ], $overrides));

        return $vacancy->fresh();
    }
}
