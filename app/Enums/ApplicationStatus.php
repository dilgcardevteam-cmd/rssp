<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'Pending';
    case COMPLIANCE = 'Compliance';
    case UPDATED = 'Updated';
    case QUALIFIED = 'Qualified';
    case READY = 'ready';
    case IN_PROGRESS = 'in-progress';
    case SUBMITTED = 'submitted';
    case REVIEWED = 'Reviewed';
    case ONGOING = 'Ongoing';
    case PASSED = 'Passed';
    case FAILED = 'Failed';
    case WITHDRAWN = 'Withdrawn';

    public static function normalize(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $raw = trim($status);
        if ($raw === '') {
            return $raw;
        }

        $lookup = self::normalizedLookup();
        $key = strtolower($raw);

        return $lookup[$key] ?? $raw;
    }

    public static function equals(?string $left, string|self|null $right): bool
    {
        $leftNormalized = self::normalize($left);
        $rightValue = $right instanceof self ? $right->value : $right;
        $rightNormalized = self::normalize($rightValue);

        return $leftNormalized !== null
            && $rightNormalized !== null
            && $leftNormalized === $rightNormalized;
    }

    public static function complianceStages(): array
    {
        return [
            self::COMPLIANCE->value,
            self::UPDATED->value,
        ];
    }

    private static function normalizedLookup(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];
        foreach (self::cases() as $case) {
            $map[strtolower($case->value)] = $case->value;
        }

        // Friendly aliases from legacy data/input.
        $map['in progress'] = self::IN_PROGRESS->value;
        $map['in_progress'] = self::IN_PROGRESS->value;

        return $map;
    }
}

