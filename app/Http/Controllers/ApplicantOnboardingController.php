<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Support\ApplicantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApplicantOnboardingController extends Controller
{
    public function show()
    {
        return redirect()
            ->route('dashboard_user')
            ->with('open_onboarding_modal', true);
    }

    public function store(Request $request)
    {
        $validated = $request->validateWithBag('onboarding', [
            'preferred_vacancy_id' => ['required', 'string', 'exists:job_vacancies,vacancy_id'],
            'readiness_education' => ['required', 'in:yes,no'],
            'readiness_experience' => ['required', 'in:yes,no'],
            'readiness_training' => ['required', 'in:yes,no'],
            'readiness_eligibility' => ['required', 'in:yes,no'],
            'attest_truthful' => ['accepted'],
            'attest_accountability' => ['accepted'],
        ], [
            'preferred_vacancy_id.required' => 'Please select your preferred position.',
            'attest_truthful.accepted' => 'Please confirm that all declarations are true before submitting onboarding.',
            'attest_accountability.accepted' => 'Please acknowledge accountability and screening consequences.',
        ]);

        $vacancy = JobVacancy::query()
            ->where('vacancy_id', trim((string) $validated['preferred_vacancy_id']))
            ->where('status', 'OPEN')
            ->whereRaw('DATE(closing_date) >= DATE(NOW())')
            ->first();

        if (!$vacancy) {
            return back()
                ->withInput()
                ->withErrors([
                    'preferred_vacancy_id' => 'The selected position is no longer open. Please choose another position.',
                ], 'onboarding');
        }

        $requirementSnapshot = [
            'education' => $this->normalizeRequirementText($vacancy->qualification_education),
            'experience' => $this->normalizeRequirementText($vacancy->qualification_experience),
            'training' => $this->normalizeRequirementText($vacancy->qualification_training),
            'eligibility' => $this->normalizeEligibilityRequirementText($vacancy->qualification_eligibility),
        ];
        $preferredPositionTitle = (string) $vacancy->position_title;

        $readiness = [
            'education' => $validated['readiness_education'],
            'experience' => $validated['readiness_experience'],
            'training' => $validated['readiness_training'],
            'eligibility' => $validated['readiness_eligibility'],
        ];

        $missingRequirementKeys = collect($readiness)
            ->filter(fn (string $answer) => strtolower($answer) !== 'yes')
            ->keys()
            ->values()
            ->all();

        $allRequirementsReady = empty($missingRequirementKeys);

        $payload = [
            'completed_at' => now()->toIso8601String(),
            'preferred_vacancy_id' => $vacancy ? (string) $vacancy->vacancy_id : null,
            'preferred_position_title' => $preferredPositionTitle,
            'requirements' => $requirementSnapshot,
            'readiness' => $readiness,
            'missing_requirements' => $missingRequirementKeys,
            'all_requirements_ready' => $allRequirementsReady,
            'attestation' => [
                'truthful' => true,
                'accountability' => true,
            ],
            'security_context' => [
                'ip' => (string) $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            ],
        ];

        $user = Auth::user();
        ApplicantOnboarding::save($user, $payload);

        activity()
            ->causedBy($user)
            ->event('onboarding')
            ->withProperties([
                'preferred_vacancy_id' => $vacancy->vacancy_id,
                'preferred_position_title' => $preferredPositionTitle,
                'all_requirements_ready' => $allRequirementsReady,
                'missing_requirements' => $missingRequirementKeys,
                'section' => 'Applicant Onboarding',
            ])
            ->log('Completed applicant onboarding flow.');

        $missingRequirementLabels = collect($missingRequirementKeys)
            ->map(fn (string $key) => $this->toReadableRequirementLabel($key))
            ->filter()
            ->values()
            ->all();

        if ($allRequirementsReady) {
            return redirect()->route('dashboard_user')
                ->with('success', 'Onboarding completed. You can now continue with your application.');
        }

        return redirect()->route('dashboard_user')->with(
            'error',
            'Onboarding saved. You marked the following as not ready yet: '
            . implode(', ', $missingRequirementLabels)
            . '. Please complete your PDS and required documents before applying.'
        );
    }

    private function normalizeRequirementText(?string $raw): string
    {
        $text = trim((string) $raw);
        if ($text === '') {
            return 'Not specified';
        }

        return preg_replace('/\s+/', ' ', $text) ?: $text;
    }

    private function normalizeEligibilityRequirementText(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 'Not specified';
        }

        $parsed = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            return $this->normalizeRequirementText($raw);
        }

        $records = array_key_exists('name', $parsed) ? [$parsed] : $parsed;
        $labels = [];
        foreach ($records as $record) {
            if (is_string($record)) {
                $label = trim($record);
                if ($label !== '') {
                    $labels[] = $label;
                }
                continue;
            }

            if (!is_array($record)) {
                continue;
            }

            $name = trim((string) ($record['name'] ?? ''));
            $level = trim((string) ($record['level'] ?? ''));
            $legalBasis = trim((string) ($record['legalBasis'] ?? $record['legal_basis'] ?? ''));
            if ($name === '') {
                continue;
            }

            $parts = [];
            if ($level !== '') {
                $parts[] = $level;
            }
            if ($legalBasis !== '') {
                $parts[] = $legalBasis;
            }

            $labels[] = empty($parts) ? $name : ($name . ' (' . implode(' | ', $parts) . ')');
        }

        if (empty($labels)) {
            return 'Not specified';
        }

        return implode("\n", $labels);
    }

    private function toReadableRequirementLabel(string $key): string
    {
        return match ($key) {
            'education' => 'Education',
            'experience' => 'Experience',
            'training' => 'Training',
            'eligibility' => 'Eligibility',
            default => '',
        };
    }
}
