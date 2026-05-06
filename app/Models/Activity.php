<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    protected static function booted(): void
    {
        static::creating(function (self $activity): void {
            $properties = self::normalizeProperties($activity->properties);

            $actorEmail = self::resolveActorEmail($activity);
            $subjectEmail = self::resolveSubjectEmail($activity);
            $targetUserEmail = self::resolveTargetUserEmail($activity, $subjectEmail);

            if ($actorEmail !== null && $actorEmail !== '') {
                $properties['actor_email'] = $actorEmail;
            }

            if ($subjectEmail !== null && $subjectEmail !== '') {
                $properties['subject_email'] = $subjectEmail;
            }

            if ($targetUserEmail !== null && $targetUserEmail !== '') {
                $properties['target_user_email'] = $targetUserEmail;
            }

            $activity->properties = $properties;
        });
    }

    /**
     * @param mixed $properties
     * @return array<string, mixed>
     */
    private static function normalizeProperties($properties): array
    {
        if ($properties instanceof Collection) {
            return $properties->toArray();
        }

        if (is_array($properties)) {
            return $properties;
        }

        if (is_string($properties) && trim($properties) !== '') {
            $decoded = json_decode($properties, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private static function resolveActorEmail(self $activity): ?string
    {
        $email = self::resolveEmailByMorph($activity->causer_type, $activity->causer_id);
        if ($email !== null) {
            return $email;
        }

        $authActor = auth('admin')->user() ?? auth()->user();
        if (is_object($authActor) && isset($authActor->email)) {
            $value = trim((string) $authActor->email);
            return $value !== '' ? $value : null;
        }

        return null;
    }

    private static function resolveSubjectEmail(self $activity): ?string
    {
        $direct = self::resolveEmailByMorph($activity->subject_type, $activity->subject_id);
        if ($direct !== null) {
            return $direct;
        }

        $subject = self::resolveModelByMorph($activity->subject_type, $activity->subject_id);
        if (!$subject) {
            return null;
        }

        if (method_exists($subject, 'user')) {
            $relatedUser = $subject->user;
            if (is_object($relatedUser) && isset($relatedUser->email)) {
                $email = trim((string) $relatedUser->email);
                if ($email !== '') {
                    return $email;
                }
            }
        }

        return null;
    }

    private static function resolveTargetUserEmail(self $activity, ?string $subjectEmail): ?string
    {
        if ($subjectEmail !== null && $subjectEmail !== '') {
            return $subjectEmail;
        }

        $properties = self::normalizeProperties($activity->properties);
        $userId = $properties['user_id'] ?? null;
        if (is_numeric($userId)) {
            $user = User::query()->find((int) $userId);
            if ($user && isset($user->email)) {
                $email = trim((string) $user->email);
                return $email !== '' ? $email : null;
            }
        }

        return null;
    }

    private static function resolveEmailByMorph(?string $type, $id): ?string
    {
        $model = self::resolveModelByMorph($type, $id);
        if (!$model || !isset($model->email)) {
            return null;
        }

        $email = trim((string) $model->email);
        return $email !== '' ? $email : null;
    }

    private static function resolveModelByMorph(?string $type, $id): ?object
    {
        if (!is_string($type) || trim($type) === '' || empty($id) || !class_exists($type)) {
            return null;
        }

        if (!method_exists($type, 'query')) {
            return null;
        }

        return $type::query()->find($id);
    }
}
