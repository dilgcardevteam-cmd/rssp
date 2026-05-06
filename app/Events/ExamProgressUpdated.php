<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $vacancyId,
        public int $userId,
        public string $type,
        public ?string $status = null,
        public ?int $tabViolations = null,
        public ?string $occurredAt = null,
        public array $meta = [],
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('exam-monitor.' . $this->vacancyId),
            new PrivateChannel('exam-participant.' . $this->vacancyId . '.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'exam.progress.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'vacancy_id' => $this->vacancyId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'status' => $this->status,
            'tab_violations' => $this->tabViolations,
            'occurred_at' => $this->occurredAt,
            'meta' => $this->meta,
        ];
    }
}
