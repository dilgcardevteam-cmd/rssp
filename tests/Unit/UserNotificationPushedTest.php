<?php

namespace Tests\Unit;

use App\Events\UserNotificationPushed;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class UserNotificationPushedTest extends TestCase
{
    public function test_notification_event_targets_user_notification_channel(): void
    {
        $event = new UserNotificationPushed(42, 'notif-123', [
            'title' => 'Exam Attendance Confirmation',
        ]);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-notifications.42', $channels[0]->name);
        $this->assertSame('NewSystemNotification', $event->broadcastAs());
        $this->assertSame([
            'id' => 'notif-123',
            'data' => ['title' => 'Exam Attendance Confirmation'],
        ], $event->broadcastWith());
    }
}
