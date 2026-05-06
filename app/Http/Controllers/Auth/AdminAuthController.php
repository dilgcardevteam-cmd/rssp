<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    private function generateUniqueUsername(string $firstName, string $lastName, string $email): string
    {
        $nameBase = Str::slug(trim($firstName . ' ' . $lastName), '_');
        $emailBase = Str::slug((string) Str::before($email, '@'), '_');
        $base = Str::lower($nameBase !== '' ? $nameBase : ($emailBase !== '' ? $emailBase : 'employee'));

        $candidate = $base;
        $suffix = 1;
        while (Admin::where('username', $candidate)->exists()) {
            $candidate = $base . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function redirectByAdminRole($user, bool $useIntended = false)
    {
        $approvalStatus = (string) ($user->approval_status ?? 'approved');

        if ($approvalStatus === 'pending') {
            return redirect()->route('admin.pending.dashboard');
        }

        if ($approvalStatus === 'declined') {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your account request was declined. Please contact superadmin.',
            ]);
        }

        return match ($user->role ?? null) {
            'viewer' => redirect()->route('viewer'),
            'hr_division' => $useIntended ? redirect()->intended('/admin/dashboard') : redirect()->route('dashboard_admin'),
            default => $useIntended ? redirect()->intended('/admin/dashboard') : redirect()->route('dashboard_admin'),
        };
    }

    private function sanitizeAdminIntendedRedirect(Request $request): void
    {
        $intendedUrl = (string) $request->session()->get('url.intended', '');

        if ($intendedUrl === '') {
            return;
        }

        $intendedPath = (string) parse_url($intendedUrl, PHP_URL_PATH);

        if ($intendedPath === '' || !Str::startsWith($intendedPath, '/admin')) {
            $request->session()->forget('url.intended');
        }
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
            'redirect_after_login',
            'pending_registration',
        ]);
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return $this->redirectByAdminRole(Auth::guard('admin')->user());
        }

        if (Auth::check()) {
            return redirect()->route('dashboard_user');
        }

        return view('login_register.admin_login');
    }

    public function register(Request $request)
    {
        if (Auth::check()) {
            $this->clearPdsSessionCache($request);
            Auth::logout();
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
            ],
        ], [
            'password.regex' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.',
            'email.unique' => 'The email has already been taken.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'adminRegister')
                ->withInput()
                ->with('auth_tab', 'register');
        }

        $validated = $validator->validated();
        $fullName = trim(implode(' ', array_filter([
            trim((string) ($validated['first_name'] ?? '')),
            trim((string) ($validated['middle_name'] ?? '')),
            trim((string) ($validated['last_name'] ?? '')),
        ])));
        $generatedUsername = $this->generateUniqueUsername(
            (string) ($validated['first_name'] ?? ''),
            (string) ($validated['last_name'] ?? ''),
            (string) ($validated['email'] ?? '')
        );

        $admin = Admin::create([
            'username' => $generatedUsername,
            'name' => $fullName,
            'office' => $validated['office'],
            'designation' => $validated['designation'],
            'email' => $validated['email'],
            'password' => Hash::make((string) $validated['password']),
            'role' => 'viewer',
            'is_active' => 1,
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'declined_at' => null,
        ]);

        $superadmins = Admin::query()
            ->where('role', 'superadmin')
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('approval_status')->orWhere('approval_status', 'approved');
            })
            ->get(['id']);

        foreach ($superadmins as $superadmin) {
            Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $superadmin->id,
                'type' => 'info',
                'data' => [
                    'category' => 'account_approval',
                    'title' => 'New Employee Registration',
                    'message' => $fullName . ' registered and is awaiting approval.',
                    'action_url' => route('admin_account_management', [], false),
                    'registered_admin_id' => $admin->id,
                    'level' => 'warning',
                ],
            ]);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        activity()
            ->causedBy($admin)
            ->withProperties(['section' => 'Login'])
            ->event('register')
            ->log('Admin account registered and pending approval.');

        return redirect()->route('admin.pending.dashboard')
            ->with('registered_pending', 'Registration submitted. Wait for superadmin approval.');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            $this->clearPdsSessionCache($request);
            Auth::logout();
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = trim((string) ($credentials['email'] ?? ''));
        $password = (string) ($credentials['password'] ?? '');

        $admin = Admin::query()
            ->where('email', $email)
            ->first();

        $emailMatchesCase = $admin && hash_equals((string) $admin->email, $email);
        $passwordMatches = $admin && Hash::check($password, (string) $admin->password);

        if ($emailMatchesCase && $passwordMatches) {
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            $user = Auth::guard('admin')->user();
            $approvalStatus = (string) ($user->approval_status ?? 'approved');

            if (($user->is_active ?? 0) != 1) {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ])->with('auth_tab', 'login');
            }

            if ($approvalStatus === 'declined') {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Your account request was declined. Please contact superadmin.',
                ])->with('auth_tab', 'login');
            }

            activity()
                ->causedBy($user)
                ->withProperties(['section' => 'Login'])
                ->event('login')
                ->log('Admin logged in successfully.');

            $this->sanitizeAdminIntendedRedirect($request);

            return $this->redirectByAdminRole($user, true);
        }

        activity()
            ->withProperties(['ip' => $request->ip(), 'email' => $request->email, 'section' => 'Login'])
            ->event('login_failed')
            ->log('Failed admin login attempt.');

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput()->with('auth_tab', 'login');
    }

    public function pendingDashboard(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();
        $approvalStatus = (string) ($admin->approval_status ?? 'approved');

        if ($approvalStatus === 'approved') {
            return $this->redirectByAdminRole($admin);
        }

        if ($approvalStatus === 'declined') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your account request was declined. Please contact superadmin.',
            ]);
        }

        return view('admin.pending_dashboard', compact('admin'));
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $this->clearPdsSessionCache($request);
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        activity()
            ->withProperties(['section' => 'Login'])
            ->causedBy($admin)
            ->log('Admin logged out.');

        return redirect('/admin/login')
            ->header('Clear-Site-Data', '"cache","storage"');
    }
}
