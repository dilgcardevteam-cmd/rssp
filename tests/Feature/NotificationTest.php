<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewSystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_database_notification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->notify(new NewSystemNotification('Title', 'Message', 'info'));
        $this->assertSame(1, $user->notifications()->count());
        $res = $this->getJson(route('notifications.count'));
        $res->assertOk()->assertJson(['count' => 1]);
    }
}

