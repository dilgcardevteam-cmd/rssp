<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ApplicantsAccess
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

        if (!Gate::forUser($admin)->allows('admin.applicants.monitor')) {
            if (Gate::forUser($admin)->allows('admin.exam.monitor')) {
                return redirect()->route('viewer')
                    ->with('error', 'Access denied. Viewer can only access exam management.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
