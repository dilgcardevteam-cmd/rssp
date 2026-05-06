<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Response;

class AdminEmailLogController extends Controller
{
    public function show(EmailLog $emailLog)
    {
        return view('admin.email_log_show', [
            'emailLog' => $emailLog,
        ]);
    }

    public function html(EmailLog $emailLog): Response
    {
        $html = (string) ($emailLog->body_html ?? '');
        if (trim($html) == '') {
            $html = '<p>No HTML content available.</p>';
        }

        $html = $this->rewriteCidImagesForPreview($html);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            // Prevent scripts/unsafe navigation from executing inside the preview.
            ->header(
                'Content-Security-Policy',
                "default-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'; img-src data: https: http:; style-src 'unsafe-inline'; font-src data: https: http:; script-src 'none';"
            );
    }

    private function rewriteCidImagesForPreview(string $html): string
    {
        if (! str_contains($html, 'cid:')) {
            return $html;
        }

        $logoReplacement = $this->dilgLogoDataUri() ?: asset('images/dilg_logo.png');

        // Most of our mail templates use $message->embed(...) for the logo, producing src="cid:...".
        // Browsers don't render cid: URLs, so we rewrite them for the admin HTML preview.
        $html = (string) preg_replace(
            '/(<img\b[^>]*\bsrc=)(["\'])cid:[^"\']*\2/i',
            '$1$2' . $logoReplacement . '$2',
            $html
        );

        // Also handle unquoted variants: src=cid:...
        $html = (string) preg_replace(
            '/(<img\b[^>]*\bsrc=)cid:[^\s>]+/i',
            '$1' . $logoReplacement,
            $html
        );

        // And CSS url(cid:...)
        $html = (string) preg_replace(
            '/url\((?:["\']?)cid:[^\)\s]+(?:["\']?)\)/i',
            'url(' . $logoReplacement . ')',
            $html
        );

        return $html;
    }

    private function dilgLogoDataUri(): ?string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $path = public_path('images/dilg_logo.png');
        if (! is_file($path)) {
            return $cached = null;
        }

        $bytes = @file_get_contents($path);
        if ($bytes === false || $bytes === '') {
            return $cached = null;
        }

        return $cached = 'data:image/png;base64,' . base64_encode($bytes);
    }
}
