<?php

namespace App\Observers;

use App\Models\Applications;
use Illuminate\Support\Facades\Auth;

class ApplicationObserver
{
    private const TRACKED_FIELDS = [
        'status',
        'deadline_date',
        'deadline_time',
        'qs_result',
        'application_remarks',
        'file_status',
        'file_remarks',
        'result',
        'scores',
    ];

    public function updated(Applications $application): void
    {
        $changes = $this->extractTrackedChanges($application);
        if (empty($changes)) {
            return;
        }

        $adminActor = Auth::guard('admin')->user();
        $userActor = Auth::guard('web')->user();
        $actor = $adminActor ?? $userActor;

        $log = activity()
            ->event('audit_update')
            ->performedOn($application)
            ->withProperties([
                'section' => 'Application List',
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'changes' => $changes,
                'audit_source' => 'observer',
            ]);

        if ($actor) {
            $log->causedBy($actor);
        }

        $log->log('Application critical fields changed.');
    }

    private function extractTrackedChanges(Applications $application): array
    {
        $changedKeys = array_keys($application->getChanges());
        $trackedKeys = array_intersect($changedKeys, self::TRACKED_FIELDS);
        $changes = [];

        foreach ($trackedKeys as $field) {
            $old = $application->getOriginal($field);
            $new = $application->getAttribute($field);

            if ($old === $new) {
                continue;
            }

            $changes[$field] = [
                'old' => $this->normalizeValue($old),
                'new' => $this->normalizeValue($new),
            ];
        }

        return $changes;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return method_exists($value, 'toArray') ? $value->toArray() : (string) json_encode($value);
        }

        return $value;
    }
}

