<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class PreviewUrl
{
    public static function forPath(?string $storagePath, ?int $ttlMinutes = null): string
    {
        $normalizedPath = trim((string) $storagePath);
        if ($normalizedPath === '' || strcasecmp($normalizedPath, 'NOINPUT') === 0) {
            return '';
        }

        $minutes = $ttlMinutes ?? (int) config('security.preview_url_ttl_minutes', 15);
        if ($minutes < 1) {
            $minutes = 1;
        }

        return URL::temporarySignedRoute(
            'preview.file',
            now()->addMinutes($minutes),
            ['path' => base64_encode($normalizedPath)]
        );
    }
}

