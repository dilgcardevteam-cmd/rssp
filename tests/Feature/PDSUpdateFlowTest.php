<?php

namespace Tests\Feature;

use App\Models\WorkExperience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PDSUpdateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pds_c1_submit_redirects_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Required fields for C1
        $data = [
            'surname' => 'Doe',
            'first_name' => 'John',
            'middle_name' => 'M',
            'civil_status' => 'single',
            'date_of_birth' => '01-01-1990',
            'place_of_birth' => 'Test City',
            'citizenship' => 'Filipino',
            'sex' => 'male',
            'blood_type' => 'O+',
            'mobile_no' => '09123456789',
            'email_address' => 'john@example.com',
            'height' => 170,
            'weight' => 70,
            'elem_from' => '01-01-2000',
            'elem_to' => '01-01-2006',
            'jhs_from' => '01-01-2006',
            'jhs_to' => '01-01-2010',
            // Add other required fields if any
        ];

        $url = route('submit_c1', ['go_to' => 'c2_update']);
        $response = $this->post($url, $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('c2_update'));
    }

    public function test_pds_finalize_route_exists()
    {
        $url = route('finalize_pds', ['go_to' => 'dashboard_user']);
        $this->assertStringContainsString('/pds/finalize/dashboard_user', $url);
    }

    public function test_pds_c2_submit_updates_existing_work_experience_and_keeps_simple_mode(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workExperience = WorkExperience::query()->create([
            'user_id' => $user->id,
            'work_exp_from' => '2026-03-29',
            'work_exp_to' => '2026-04-28',
            'work_exp_position' => 'AWDA',
            'work_exp_department' => 'AWDA',
            'work_exp_status' => 'Temporary',
            'work_exp_govt_service' => 'N',
        ]);

        $payload = [
            'simple' => '1',
            'work_exp_id' => [$workExperience->id],
            'work_exp_from' => ['2026-05-04'],
            'work_exp_to' => ['present'],
            'work_exp_position' => ['AWD'],
            'work_exp_department' => ['AWD'],
            'work_exp_status' => ['Contract of Service'],
            'work_exp_govt_service' => ['N'],
            'cs_eligibility_career' => [],
            'cs_eligibility_rating' => [],
            'cs_eligibility_date' => [],
            'cs_eligibility_place' => [],
            'cs_eligibility_license' => [],
            'cs_eligibility_validity' => [],
        ];

        $response = $this->post(route('submit_c2', ['go_to' => 'display_c3']), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('display_c3', ['simple' => 1]));

        $workExperience->refresh();

        $this->assertSame('2026-05-04', $workExperience->work_exp_from);
        $this->assertSame('AWD', $workExperience->work_exp_position);
        $this->assertSame('AWD', $workExperience->work_exp_department);
        $this->assertSame('Contract of Service', $workExperience->work_exp_status);
        $this->assertSame('N', $workExperience->work_exp_govt_service);
    }
}
