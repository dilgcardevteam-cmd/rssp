<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Applications;

class ApplicationStatusTransitionService
{
    /**
     * Canonical lifecycle transitions for application and exam workflow.
     *
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'Pending' => ['Pending', 'Compliance', 'Qualified'],
        'Compliance' => ['Compliance', 'Updated', 'Pending'],
        'Updated' => ['Updated', 'Compliance', 'Qualified', 'Pending'],
        'Qualified' => ['Qualified', 'ready', 'Compliance', 'Updated'],
        'ready' => ['ready', 'in-progress', 'submitted'],
        'in-progress' => ['in-progress', 'submitted'],
        'submitted' => ['submitted'],
    ];

    public function canTransition(?string $fromStatus, string $toStatus): bool
    {
        $from = ApplicationStatus::normalize($fromStatus);
        $to = ApplicationStatus::normalize($toStatus);

        if ($to === null || $to === '') {
            return false;
        }

        if ($from === null || $from === '') {
            return true;
        }

        if ($from === $to) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    public function transition(Applications $application, string $toStatus, bool $persist = true): bool
    {
        if (!$this->canTransition($application->status, $toStatus)) {
            return false;
        }

        $application->status = ApplicationStatus::normalize($toStatus);
        if ($persist) {
            $application->save();
        }

        return true;
    }
}

