<?php

namespace Tests\Feature;

use App\Jobs\ProcessAdminActivityNotification;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminActivityNotificationQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_creation_dispatches_admin_notification_job(): void
    {
        Queue::fake();

        $admin = Admin::create([
            'username' => 'queue_admin',
            'name' => 'Queue Admin',
            'office' => 'HR',
            'designation' => 'Officer',
            'email' => 'queue-admin@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        activity()
            ->causedBy($admin)
            ->event('start')
            ->withProperties(['section' => 'Exam Management'])
            ->log('Started exam schedule.');

        Queue::assertPushed(ProcessAdminActivityNotification::class);
    }
}

