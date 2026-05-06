<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();
        if (($admin->is_active ?? 0) != 1) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $approvalStatus = (string) ($admin->approval_status ?? 'approved');
        if ($approvalStatus === 'pending') {
            return redirect()->route('admin.pending.dashboard')
                ->with('error', 'Your account is pending superadmin approval.');
        }

        if ($approvalStatus === 'declined') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account request was declined. Please contact superadmin.']);
        }

        if (Gate::forUser($admin)->denies('admin.system.manage')) {
            $target = match ($admin->role ?? null) {
                'viewer' => route('viewer'),
                'hr_division' => route('applications_list'),
                default => route('dashboard_admin'),
            };

            return redirect($target)
                ->with('error', 'Only superadmin can access user management.');
        }

        return $next($request);
    }
}
