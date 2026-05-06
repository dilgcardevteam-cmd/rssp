<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C3SubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_c3_submit_saves_data_and_preserves_simple_flag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('submit_c3', ['go_to' => 'display_c4']), [
            'simple' => 1,
            'learning_entry_count' => 1,
            'learning_title_1' => 'Sample Training',
            'learning_type_1' => 'Technical',
            'learning_from_1' => '2026-03-01',
            'learning_to_1' => '2026-03-02',
            'learning_hours_1' => 24,
            'learning_conducted_1' => 'Sample Training Provider',
            'voluntary_work_count' => 1,
            'voluntary_org_1' => 'Sample Org',
            'voluntary_from_1' => '2026-02-01',
            'voluntary_to_1' => '2026-02-02',
            'voluntary_hours_1' => 8,
            'voluntary_position_1' => 'Volunteer',
            'skills' => ['Skill A'],
            'distinctions' => ['Distinction A'],
            'organizations' => ['Organization A'],
        ]);

        $response->assertRedirect(route('display_c4', ['simple' => 1]));
        $this->assertDatabaseHas('learning_and_developments', [
            'user_id' => $user->id,
            'learning_title' => 'Sample Training',
            'learning_hours' => 24,
        ]);
        $this->assertDatabaseHas('voluntary_works', [
            'user_id' => $user->id,
            'voluntary_org' => 'Sample Org',
            'voluntary_hours' => 8,
        ]);
        $this->assertDatabaseHas('other_information', [
            'user_id' => $user->id,
        ]);
    }

    public function test_c3_submit_rejects_learning_hours_over_smallint_limit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('submit_c3', ['go_to' => 'display_c4']), [
            'simple' => 1,
            'learning_entry_count' => 1,
            'learning_title_1' => 'Sample Training',
            'learning_type_1' => 'Technical',
            'learning_from_1' => '2026-03-01',
            'learning_to_1' => '2026-03-02',
            'learning_hours_1' => 40000,
            'learning_conducted_1' => 'Sample Training Provider',
            'voluntary_work_count' => 0,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['learning_hours_1']);
        $this->assertDatabaseMissing('learning_and_developments', [
            'user_id' => $user->id,
            'learning_title' => 'Sample Training',
        ]);
    }

    public function test_c3_submit_rejects_voluntary_hours_over_smallint_limit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('submit_c3', ['go_to' => 'display_c4']), [
            'simple' => 1,
            'learning_entry_count' => 0,
            'voluntary_work_count' => 1,
            'voluntary_org_1' => 'Sample Org',
            'voluntary_from_1' => '2026-02-01',
            'voluntary_to_1' => '2026-02-02',
            'voluntary_hours_1' => 40000,
            'voluntary_position_1' => 'Volunteer',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['voluntary_hours_1']);
        $this->assertDatabaseMissing('voluntary_works', [
            'user_id' => $user->id,
            'voluntary_org' => 'Sample Org',
        ]);
    }
}

