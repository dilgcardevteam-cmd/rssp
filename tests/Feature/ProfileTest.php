<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_pages_require_auth(): void
    {
        $this->get(route('profile.show'))->assertRedirect();
        $this->get(route('profile.edit'))->assertRedirect();
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $res = $this->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
            'bio' => 'Hello',
        ]);
        $res->assertRedirect(route('account.settings'));
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('Hello', $user->bio);
    }
}
