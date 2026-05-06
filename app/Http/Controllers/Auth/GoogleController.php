<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApplicantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function isLocalDevHost(string $host): bool
    {
        $host = strtolower(trim($host));
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        return (bool) preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[0-1])\.)/', $host);
    }

    private function shouldPreferRequestHost(Request $request, string $configuredHost): bool
    {
        if (!app()->environment('local')) {
            return false;
        }

        $requestHost = strtolower(trim((string) $request->getHost()));
        $configuredHost = strtolower(trim($configuredHost));
        if ($configuredHost === '0.0.0.0') {
            $configuredHost = '127.0.0.1';
        }

        if ($requestHost === $configuredHost) {
            return false;
        }

        return $this->isLocalDevHost($requestHost) && $this->isLocalDevHost($configuredHost);
    }

    private function resolveGoogleRedirectUrl(Request $request): string
    {
        $configuredRedirect = trim((string) config('services.google.redirect', ''));
        $defaultPath = '/auth/google/callback';
        $requestHost = $request->getHost() === '0.0.0.0' ? '127.0.0.1' : $request->getHost();
        $port = (int) $request->getPort();
        $scheme = $request->getScheme();
        $isDefaultPort = ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
        $portSuffix = $isDefaultPort ? '' : ':' . $port;
        $requestBasedUrl = "{$scheme}://{$requestHost}{$portSuffix}{$defaultPath}";

        if ($configuredRedirect !== '') {
            $parsed = parse_url($configuredRedirect);
            if ($parsed === false) {
                return $requestBasedUrl;
            }

            // If redirect URI in .env is absolute, use it as canonical callback URL.
            if (($parsed['scheme'] ?? null) && ($parsed['host'] ?? null)) {
                $host = $parsed['host'] === '0.0.0.0' ? '127.0.0.1' : $parsed['host'];
                if ($this->shouldPreferRequestHost($request, $host)) {
                    return $requestBasedUrl;
                }
                $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $path = $parsed['path'] ?? $defaultPath;
                if (!str_starts_with($path, '/')) {
                    $path = '/' . $path;
                }
                $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';

                return "{$parsed['scheme']}://{$host}{$port}{$path}{$query}";
            }

            $path = $parsed['path'] ?? $defaultPath;
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
            $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
            return url($path . $query);
        }

        return $requestBasedUrl;
    }

    private function clearPdsSessionCache(Request $request): void
    {
        $request->session()->forget([
            'form',
            'data_learning',
            'data_voluntary',
            'data_otherInfo',
            'vacancy_doc_uploads',
            'pds_form_owner',
        ]);
    }

    public function redirectToGoogle(Request $request)
    {
        $redirectUrl = $this->resolveGoogleRedirectUrl($request);

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('login.form')->withErrors([
                'google' => 'Google sign-in was cancelled or blocked. Please try again.',
            ]);
        }

        try {
            $redirectUrl = $this->resolveGoogleRedirectUrl($request);
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->stateless()
                ->user();
        } catch (\Throwable $e) {
            activity()
                ->withProperties([
                    'ip' => request()->ip(),
                    'section' => 'Google Login',
                    'error' => $e->getMessage(),
                ])
                ->event('login_failed')
                ->log('google oauth callback failed');

            return redirect()->route('login.form')->withErrors([
                'google' => 'Google sign-in failed. Verify your Google OAuth redirect URI, then try again.',
            ]);
        }

        if (empty($googleUser->getEmail())) {
            return redirect()->route('login.form')->withErrors([
                'google' => 'Google account has no email address. Use a different Google account.',
            ]);
        }

        $googleFullName = trim((string) $googleUser->getName());
        $nameParts = preg_split('/\s+/', $googleFullName) ?: [];
        $firstName = (string) ($nameParts[0] ?? '');
        $lastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
        $middleName = count($nameParts) > 2
            ? trim(implode(' ', array_slice($nameParts, 1, -1)))
            : '';
        $middleInitial = $middleName !== '' ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';
        $fullName = trim(implode(' ', array_filter([$firstName, $middleInitial, $lastName])));

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $fullName !== '' ? $fullName : $googleFullName,
                'first_name' => $firstName !== '' ? $firstName : null,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'password' => bcrypt('google-oauth'),
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();
        $this->clearPdsSessionCache($request);

        activity()
            ->withProperties(['ip' => request()->ip(), 'section' => 'Google Login'])
            ->causedBy(auth()->user())
            ->event('login')
            ->log('login through google');

        if (ApplicantOnboarding::shouldRequire($user)) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete your onboarding before submitting applications.');
        }

        return redirect()->route('dashboard_user');
    }
}
