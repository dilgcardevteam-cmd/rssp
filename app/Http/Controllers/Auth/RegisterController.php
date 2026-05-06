<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use App\Mail\OTPmail;

use Spatie\Activitylog\Models\Activity;

class RegisterController extends Controller
{
    private function canUseLocalOtpFallback(): bool
    {
        return filter_var((string) env('OTP_LOCAL_FALLBACK', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    private function getOtpMailFailureMessage(\Throwable $e): string
    {
        $errorMessage = strtolower((string) $e->getMessage());

        if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'username and password not accepted')) {
            return 'OTP email service authentication failed. Please contact support to refresh the mail app password.';
        }

        return 'Unable to send OTP right now. Please try again after a moment.';
    }

    public function showRegistrationForm()
    {
    /*
        activity()
            ->log('Viewed registration form.');
    */
        return view('login_register.register');
    }

    public function register(Request $request)
    {
        // Support both field naming styles:
        // first_name/middle_name/last_name and fname/mname/lname.
        $firstName = trim((string) ($request->input('first_name') ?? $request->input('fname') ?? ''));
        $middleName = trim((string) ($request->input('middle_name') ?? $request->input('middle_initial') ?? $request->input('mname') ?? ''));
        $lastName = trim((string) ($request->input('last_name') ?? $request->input('lname') ?? ''));
        $phoneNumber = preg_replace('/\D+/', '', (string) $request->input('phone_number', ''));

        $request->merge([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber,
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'sex' => 'nullable|string|max:20',
        ], [
            'phone_number.regex' => 'Contact number must follow the format 09XX XXX XXXX.',
        ]);

        $fullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName !== '' ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '',
            $lastName,
        ], fn ($value) => $value !== '')));

        // Generate OTP
        $otp = (string) random_int(100000, 999999);



        // Store registration data temporarily in session
        session([
            'pending_registration' => [
                'name' => $fullName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'phone_number' => $request->input('phone_number'),
                'sex' => $request->input('sex'),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp' => (string) $otp,
                'expires_at' => now()->addMinutes(5), //
                'resend_available_at_ts' => now()->addSeconds(30)->timestamp,
            ]
        ]);

        //info(now());
        //info(now()->addMinutes(5));
        try {
            Mail::to($request->email)->send(new OTPmail($otp));
            Log::info('Registration OTP mail sent.', ['email' => $request->email]);

            $pending = session('pending_registration', []);
            if (is_array($pending)) {
                $pending['mail_delivery_failed'] = false;
                session(['pending_registration' => $pending]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send registration OTP mail.', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Register'])
                ->event('send')
                ->log('Failed to send OTP via mail.');

            $fallbackEnabled = $this->canUseLocalOtpFallback();
            Log::warning('Registration OTP mail failure fallback evaluation.', [
                'email' => $request->email,
                'fallback_enabled' => $fallbackEnabled,
                'app_env' => app()->environment(),
                'app_debug' => (bool) config('app.debug'),
            ]);

            if ($fallbackEnabled) {
                $pending = session('pending_registration', []);
                if (is_array($pending)) {
                    $pending['mail_delivery_failed'] = true;
                    session(['pending_registration' => $pending]);
                }

                return redirect()->route('otp')->with('status', 'Email delivery failed, but local OTP fallback is active. Use the code shown below.');
            }

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $this->getOtpMailFailureMessage($e)]);
        }
        //info("mail");

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Register'])
            ->event('register')
            ->log('Started registration and sent OTP.');

        return redirect()->route('otp')->with('status', 'Enter the OTP sent to your email.');
    }

    public function OTPForm()
{
    $data = session('pending_registration');

    if (!$data) {
        return redirect()->route('register.form')->withErrors(['expired' => 'Session expired. Please register again.']);
    }

    activity()
        ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
        ->event('view')
        ->log('Viewed OTP input form.');


    $resendAvailableAtTs = (int) ($data['resend_available_at_ts'] ?? 0);
    if ($resendAvailableAtTs <= 0 && isset($data['resend_available_at'])) {
        $resendAvailableAtTs = Carbon::parse($data['resend_available_at'])->timestamp;
    }
    if ($resendAvailableAtTs <= 0) {
        $resendAvailableAtTs = now()->timestamp;
    }

    return view('login_register.otp', [
        'email' => $data['email'],
        'status' => 'otp_waiting',
        'serverNowTs' => now()->timestamp,
        'resendAvailableAtTs' => $resendAvailableAtTs,
        'fallbackOtp' => ($this->canUseLocalOtpFallback() && !empty($data['mail_delivery_failed'])) ? (string) ($data['otp'] ?? '') : '',
    ]);
}


    public function OTPCheck(Request $request)
    {
        //info("otp_check");
        $request->validate([
            'otp' => 'required|string|max:20',
        ]);


        $data = session('pending_registration');

        //info("data_check");
        if (!$data) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                ->event('verify')
                    ->log('OTP expired during registration.');

            return redirect()->route('register')->withErrors(['expired' => 'Session expired. Please register again.']);
        }

        //info("exp_check");
        if (now()->gt($data['expires_at'])) {
            session()->forget('pending_registration');

            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                    ->event('verify')
                ->log('OTP expired during registration.');

            return redirect()->route('register')->withErrors(['expired' => 'OTP expired. Please register again.']);
        }

        $submittedOtp = preg_replace('/\D+/', '', (string) $request->input('otp', ''));
        $expectedOtp = preg_replace('/\D+/', '', (string) ($data['otp'] ?? ''));

        if ($submittedOtp === '' || $expectedOtp === '' || !hash_equals($expectedOtp, $submittedOtp)) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                    ->event('verify')
                ->log('Entered invalid OTP.');

            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // OTP correct and not expired, create user
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'sex' => $data['sex'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        session()->forget('pending_registration'); // Clean up session

        // auth()->login($user);

        activity()
            ->causedBy($user)
            ->event('verify')
            ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Register'])
            ->log('Completed registration and verified email.');

        return redirect()->route('login.form')->with('success', 'Account verified successfully! You may now log in.');
    }

    public function resendOTP(Request $request)
    {
        $data = session('pending_registration');
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->isJson();

        if (!$data) {
            return $wantsJson
                ? response()->json(['message' => 'Session expired.'], 419)
                : redirect()->route('register')->withErrors(['expired' => 'Session expired. Please register again.']);
        }

        $resendAvailableAtTs = (int) ($data['resend_available_at_ts'] ?? 0);
        if ($resendAvailableAtTs <= 0 && isset($data['resend_available_at'])) {
            $resendAvailableAtTs = Carbon::parse($data['resend_available_at'])->timestamp;
        }

        $nowTs = now()->timestamp;
        if ($resendAvailableAtTs > $nowTs) {
            $retryAfter = $resendAvailableAtTs - $nowTs;
            $message = "Please wait {$retryAfter} seconds before requesting another OTP.";

            return $wantsJson
                ? response()->json([
                    'message' => $message,
                    'retry_after' => $retryAfter,
                    'resend_available_at' => $resendAvailableAtTs,
                    'server_now' => $nowTs,
                ], 429)
                : back()->withErrors(['otp' => $message]);
        }

        $currentOtp = preg_replace('/\D+/', '', (string) ($data['otp'] ?? ''));
        $isCurrentOtpUsable = $currentOtp !== ''
            && !empty($data['expires_at'])
            && now()->lte(Carbon::parse((string) $data['expires_at']));

        // Re-send the same unexpired OTP to avoid invalid-code confusion from multiple emails.
        $newOtp = $isCurrentOtpUsable ? $currentOtp : (string) random_int(100000, 999999);
        $data['otp'] = (string) $newOtp;
        $data['expires_at'] = now()->addMinutes(5);
        $data['resend_available_at_ts'] = now()->addSeconds(30)->timestamp;
        session(['pending_registration' => $data]);

        try {
            Mail::to($data['email'])->send(new OTPmail($newOtp));
            Log::info('Registration OTP mail resent.', ['email' => $data['email'] ?? null]);
            $data['mail_delivery_failed'] = false;
            session(['pending_registration' => $data]);
        } catch (\Throwable $e) {
            Log::error('Failed to resend registration OTP mail.', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                ->event('send')
                ->log('Failed to resend OTP via mail.');

            $fallbackEnabled = $this->canUseLocalOtpFallback();
            Log::warning('Registration OTP resend failure fallback evaluation.', [
                'email' => $data['email'] ?? null,
                'fallback_enabled' => $fallbackEnabled,
                'app_env' => app()->environment(),
                'app_debug' => (bool) config('app.debug'),
            ]);

            if ($fallbackEnabled) {
                $data['mail_delivery_failed'] = true;
                session(['pending_registration' => $data]);

                return $wantsJson
                    ? response()->json([
                        'message' => 'Email delivery failed. Local fallback OTP is available.',
                        'retry_after' => 30,
                        'resend_available_at' => (int) $data['resend_available_at_ts'],
                        'server_now' => now()->timestamp,
                        'fallback_otp' => (string) $newOtp,
                    ])
                    : back()->with('status', 'Email delivery failed. Local fallback OTP is available on the OTP page.');
            }

            return $wantsJson
                ? response()->json([
                    'message' => $this->getOtpMailFailureMessage($e),
                    'server_now' => now()->timestamp,
                ], 500)
                : back()->withErrors(['otp' => $this->getOtpMailFailureMessage($e)]);
        }

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
            ->event('send')
            ->log('Resent OTP for registration.');

        return $wantsJson
            ? response()->json([
                'message' => 'OTP resent.',
                'retry_after' => 30,
                'resend_available_at' => (int) $data['resend_available_at_ts'],
                'server_now' => now()->timestamp,
            ])
            : back()->with('status', 'A new OTP has been sent to your email.');
    }



}
