<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Mail\ResetOTPmail;
use Spatie\Activitylog\Models\Activity;


class ForgotPasswordController extends Controller
{
    private const RESEND_COOLDOWN_SECONDS = 30;

    private function getOtpMailFailureMessage(\Throwable $e): string
    {
        $errorMessage = strtolower((string) $e->getMessage());

        if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'username and password not accepted')) {
            return 'OTP email service authentication failed. Please contact support to refresh the mail app password.';
        }

        return 'Unable to send OTP right now. Please try again after a moment.';
    }

    private function findUserByExactEmail(string $email): ?User
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user) {
            return null;
        }

        return hash_equals((string) $user->email, $email) ? $user : null;
    }

    private function resendThrottleCacheKey(string $email): string
    {
        return 'forgot_password_otp_resend_available_at:' . sha1(strtolower(trim($email)));
    }

    private function getResendAvailableAtTs(string $email): int
    {
        $value = Cache::get($this->resendThrottleCacheKey($email));
        return is_numeric($value) ? (int) $value : 0;
    }

    private function setResendAvailableAtTs(string $email, int $availableAtTs): void
    {
        $ttlSeconds = max(1, $availableAtTs - now()->timestamp);

        Cache::put(
            $this->resendThrottleCacheKey($email),
            $availableAtTs,
            now()->addSeconds($ttlSeconds)
        );
    }

    // 1. Show forgot password email input form
    public function showForgotPasswordForm()
    {
        return view('login_register.forgot_password');
    }

    // 2. Process email input, generate OTP, send email, redirect to OTP input form
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = trim((string) $request->email);
        $user = $this->findUserByExactEmail($email);

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found.']);
        }

        // Generate OTP
        $otp = (string) random_int(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        try {
            // Send OTP email
            Mail::to($user->email)->send(new ResetOTPmail($otp));
        } catch (\Throwable $e) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('send')
                ->log('Failed to send OTP for password reset via mail.');

            return back()->withErrors(['email' => $this->getOtpMailFailureMessage($e)]);
        }

        $resendAvailableAtTs = now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp;
        $this->setResendAvailableAtTs($user->email, $resendAvailableAtTs);

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
            ->event('send')
            ->log('Sent OTP for password reset.');

        return redirect()->route('forgot.password.otp.form')->with([
            'status' => 'OTP sent to your email.',
            'email' => $user->email,
            'otpExpiresAt' => $user->otp_expires_at,
            'resendAvailableAtTs' => $resendAvailableAtTs,
        ]);
    }

    // 3. Show OTP input form
    public function showOtpForm(Request $request)
    {
        $email = session('email');
        $otpExpiresAt = session('otpExpiresAt');
        $resendAvailableAtTs = (int) session('resendAvailableAtTs', 0);

        if (!$email) {
            return redirect()->route('forgot.password.form')->withErrors(['email' => 'Session expired. Please start again.']);
        }

        if ($resendAvailableAtTs <= 0) {
            $resendAvailableAtTs = $this->getResendAvailableAtTs($email);
        }

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $email ?? 'Unknown', 'section' => 'Forgot Password'])
            ->event('view')
            ->log('Viewed OTP input form for password reset.');

        return view('login_register.forgot_password_otp', [
            'email' => $email,
            'otpExpiresAt' => $otpExpiresAt,
            'resendAvailableAtTs' => $resendAvailableAtTs,
            'serverNowTs' => now()->timestamp,
        ]);
    }

    // 4. Verify OTP and redirect to password reset form
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|max:20'
        ]);

        $email = trim((string) $request->email);
        $user = $this->findUserByExactEmail($email);
        $submittedOtp = preg_replace('/\D+/', '', (string) $request->input('otp', ''));
        $storedOtp = preg_replace('/\D+/', '', (string) ($user?->otp ?? ''));
        $otpIsValid = $user
            && !is_null($user->otp_expires_at)
            && $submittedOtp !== ''
            && $storedOtp !== ''
            && hash_equals($storedOtp, $submittedOtp)
            && Carbon::parse($user->otp_expires_at)->gt(Carbon::now());

        if (!$otpIsValid) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('verify')
                ->log('Invalid or expired OTP attempted.');

            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        // OTP verified - clear it
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Store session flag for reset form access
        session(['password_reset_verified' => $user->email]);

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Forgot Password'])
            ->event('verify')
            ->log('OTP verified for password reset.');

        return redirect()->route('forgot.password.reset.form', ['email' => $user->email]);
    }

    // 5. Show password reset form
    public function showResetForm($email)
    {
        $email = trim((string) $email);

        // Check session verification
        if (session('password_reset_verified') !== $email) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('view')
                ->log('Attempted to access reset form without OTP verification.');

            return redirect()->route('forgot.password.form')
                ->withErrors(['otp' => 'Please verify OTP first.']);
        }

        // Clear session to prevent reuse
        session()->forget('password_reset_verified');

        $user = $this->findUserByExactEmail($email);

        if (!$user) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('view')
                ->log('Invalid password reset link accessed.');

            return redirect()->route('forgot.password.form')->withErrors(['email' => 'Invalid reset link.']);
        }

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
            ->event('view')
            ->log('Viewed password reset form.');

        return view('login_register.reset_password', compact('email'));
    }

    // 6. Process password reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $email = trim((string) $request->email);
        $user = $this->findUserByExactEmail($email);

        if (!$user) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('reset')
                ->log('Password reset failed (email not found).');

            return back()->withErrors(['email' => 'Email not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Forgot Password'])
            ->event('reset')
            ->log('Password reset successfully.');

        return redirect()->route('login')->with('status', 'Password reset successful. You may now log in.');
    }

    // 7. Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = trim((string) $request->email);
        $user = $this->findUserByExactEmail($email);
        $nowTs = now()->timestamp;

        if (!$user) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $email, 'section' => 'Forgot Password'])
                ->event('send')
                ->log('Password reset failed (email not found).');

            return response()->json(['error' => 'Email not found.'], 404);
        }

        $resendAvailableAtTs = $this->getResendAvailableAtTs($email);
        if ($resendAvailableAtTs > $nowTs) {
            $retryAfter = $resendAvailableAtTs - $nowTs;
            return response()->json([
                'error' => "Please wait {$retryAfter} seconds before requesting another OTP.",
                'retry_after' => $retryAfter,
                'resend_available_at' => $resendAvailableAtTs,
                'server_now' => $nowTs,
            ], 429);
        }

        $currentOtp = preg_replace('/\D+/', '', (string) ($user->otp ?? ''));
        $isCurrentOtpUsable = $currentOtp !== ''
            && !is_null($user->otp_expires_at)
            && Carbon::parse((string) $user->otp_expires_at)->gt(Carbon::now());

        // Re-send the same unexpired OTP to avoid invalid-code confusion from multiple emails.
        $otp = $isCurrentOtpUsable ? $currentOtp : (string) random_int(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        try {
            // Send new OTP email
            Mail::to($user->email)->send(new ResetOTPmail($otp));
        } catch (\Throwable $e) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Forgot Password'])
                ->event('send')
                ->log('Failed to resend OTP for password reset via mail.');

            return response()->json([
                'error' => $this->getOtpMailFailureMessage($e),
                'server_now' => now()->timestamp,
            ], 500);
        }

        $nextResendAvailableAtTs = now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp;
        $this->setResendAvailableAtTs($email, $nextResendAvailableAtTs);
        session(['resendAvailableAtTs' => $nextResendAvailableAtTs]);

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Forgot Password'])
            ->event('send')
            ->log('Resent OTP for password reset.');

        return response()->json([
            'message' => 'New OTP sent successfully.',
            'otpExpiresAt' => Carbon::parse((string) $user->otp_expires_at)->toDateTimeString(),
            'retry_after' => self::RESEND_COOLDOWN_SECONDS,
            'resend_available_at' => $nextResendAvailableAtTs,
            'server_now' => now()->timestamp,
        ]);
    }
}
