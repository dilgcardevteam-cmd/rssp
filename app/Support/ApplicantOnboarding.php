<?php

namespace App\Support;

use App\Models\Applications;
use App\Models\PersonalInformation;
use App\Models\User;
use App\Models\WorkExpSheet;

class ApplicantOnboarding
{
    public const PREFERENCE_KEY = 'applicant_onboarding';

    public static function payload(?User $user): array
    {
        if (!$user) {
            return self::defaultPayload();
        }

        $user->loadMissing('profile');
        $preferences = $user->profile?->preferences;
        $preferences = is_array($preferences) ? $preferences : [];
        $onboarding = $preferences[self::PREFERENCE_KEY] ?? [];
        $onboarding = is_array($onboarding) ? $onboarding : [];

        $readiness = is_array($onboarding['readiness'] ?? null)
            ? $onboarding['readiness']
            : [];

        $normalizedReadiness = [
            'education' => self::normalizeReadinessValue($readiness['education'] ?? null),
            'experience' => self::normalizeReadinessValue($readiness['experience'] ?? null),
            'training' => self::normalizeReadinessValue($readiness['training'] ?? null),
            'eligibility' => self::normalizeReadinessValue($readiness['eligibility'] ?? null),
        ];

        return [
            'completed_at' => $onboarding['completed_at'] ?? null,
            'preferred_vacancy_id' => $onboarding['preferred_vacancy_id'] ?? null,
            'preferred_position_title' => $onboarding['preferred_position_title'] ?? null,
            'requirements' => is_array($onboarding['requirements'] ?? null) ? $onboarding['requirements'] : [],
            'readiness' => $normalizedReadiness,
            'missing_requirements' => is_array($onboarding['missing_requirements'] ?? null) ? $onboarding['missing_requirements'] : [],
            'all_requirements_ready' => (bool) ($onboarding['all_requirements_ready'] ?? false),
            'last_updated_at' => $onboarding['last_updated_at'] ?? null,
        ];
    }

    public static function isCompleted(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $payload = self::payload($user);
        return filled($payload['completed_at'] ?? null);
    }

    public static function isNewUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $userId = (int) $user->id;
        if ($userId <= 0) {
            return false;
        }

        $hasApplications = Applications::query()
            ->where('user_id', $userId)
            ->exists();
        if ($hasApplications) {
            return false;
        }

        $hasPersonalInformation = PersonalInformation::query()
            ->where('user_id', $userId)
            ->exists();
        if ($hasPersonalInformation) {
            return false;
        }

        $hasWorkExperienceSheet = WorkExpSheet::query()
            ->where('user_id', $userId)
            ->exists();
        if ($hasWorkExperienceSheet) {
            return false;
        }

        return true;
    }

    public static function shouldRequire(?User $user): bool
    {
        // Applicant onboarding is disabled; never force the onboarding modal.
        return false;
    }

    public static function save(User $user, array $payload): void
    {
        $profile = $user->profile()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $preferences = $profile->preferences;
        $preferences = is_array($preferences) ? $preferences : [];
        $existing = $preferences[self::PREFERENCE_KEY] ?? [];
        $existing = is_array($existing) ? $existing : [];

        $preferences[self::PREFERENCE_KEY] = array_merge(
            $existing,
            $payload,
            ['last_updated_at' => now()->toIso8601String()]
        );

        $profile->preferences = $preferences;
        $profile->save();
    }

    private static function defaultPayload(): array
    {
        return [
            'completed_at' => null,
            'preferred_vacancy_id' => null,
            'preferred_position_title' => null,
            'requirements' => [],
            'readiness' => [
                'education' => 'no',
                'experience' => 'no',
                'training' => 'no',
                'eligibility' => 'no',
            ],
            'missing_requirements' => [],
            'all_requirements_ready' => false,
            'last_updated_at' => null,
        ];
    }

    private static function normalizeReadinessValue($value): string
    {
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['yes', 'no'], true) ? $normalized : 'no';
    }
}
