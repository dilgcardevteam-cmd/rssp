<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ApplicantCodeService
{
    public function assignIfMissing(User $user): string
    {
        $currentCode = trim((string) ($user->applicant_code ?? ''));
        if ($currentCode !== '') {
            return $currentCode;
        }

        $user = User::query()->select(['id', 'applicant_code', 'created_at'])->findOrFail($user->id);
        $currentCode = trim((string) ($user->applicant_code ?? ''));
        if ($currentCode !== '') {
            return $currentCode;
        }

        $attempts = 0;

        while ($attempts < 5) {
            try {
                return DB::transaction(function () use ($user) {
                    $lockedUser = User::query()
                        ->select(['id', 'applicant_code', 'created_at'])
                        ->whereKey($user->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $existingCode = trim((string) ($lockedUser->applicant_code ?? ''));
                    if ($existingCode !== '') {
                        return $existingCode;
                    }

                    $yearSegment = $this->resolveYearSegment($lockedUser->created_at);
                    $prefix = 'APP-' . $yearSegment . '-';
                    $nextSequence = $this->nextSequenceForPrefix($prefix);
                    $candidate = $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);

                    $lockedUser->forceFill([
                        'applicant_code' => $candidate,
                    ])->saveQuietly();

                    return $candidate;
                }, 3);
            } catch (QueryException $exception) {
                $errorInfo = $exception->errorInfo;
                $isDuplicateKey = (($errorInfo[1] ?? null) === 1062);

                if (!$isDuplicateKey) {
                    throw $exception;
                }

                $attempts++;
            }
        }

        throw new QueryException('', [], new \RuntimeException('Unable to generate a unique applicant code.'));
    }

    private function resolveYearSegment($createdAt): string
    {
        $year = now()->format('Y');

        if ($createdAt instanceof \DateTimeInterface) {
            $year = $createdAt->format('Y');
        } elseif (!empty($createdAt)) {
            $year = date('Y', strtotime((string) $createdAt));
        }

        return substr($year, -2);
    }

    private function nextSequenceForPrefix(string $prefix): int
    {
        $lastCode = User::query()
            ->where('applicant_code', 'like', $prefix . '%')
            ->orderByDesc('applicant_code')
            ->lockForUpdate()
            ->value('applicant_code');

        if (!is_string($lastCode) || !str_starts_with($lastCode, $prefix)) {
            return 1;
        }

        $sequence = (int) substr($lastCode, strlen($prefix));

        return $sequence + 1;
    }
}
