<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BlockIfAdmin
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated with admin guard
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();

            if (($user->is_active ?? 0) != 1) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Your account has been deactivated.']);
            }

            $approvalStatus = (string) ($user->approval_status ?? 'approved');
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
            
            // Redirect based on admin role
            if ($user->role === 'viewer') {
                return redirect()->route('viewer')
                       ->with('error', 'Please use the viewer dashboard.');
            } elseif ($user->role === 'hr_division') {
                return redirect()->route('applications_list')
                    ->with('error', 'Please use Applicants Management.');
            } else {
                return redirect()->route('dashboard_admin')
                       ->with('error', 'Admins cannot access user pages.');
            }
        }
        
        return $next($request);
    }
}
