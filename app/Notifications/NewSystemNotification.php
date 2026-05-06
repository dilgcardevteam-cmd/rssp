<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Auth;

class NewSystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $level = 'info',
        public ?string $actionUrl = null,
        public ?array $meta = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level, // info|success|warning|error
            'action_url' => $this->actionUrl,
            'meta' => $this->meta,
        ]);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            'action_url' => $this->actionUrl,
            'meta' => $this->meta,
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('notifications.' . $this->getNotifiableId())];
    }

    protected function getNotifiableId(): int|string
    {
        return Auth::id() ?? 0;
    }

    public function broadcastType(): string
    {
        return 'NewSystemNotification';
    }
}
