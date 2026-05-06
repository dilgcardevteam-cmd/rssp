<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ViewerAccess
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        $user = Auth::guard('admin')->user();
        
        // Check if account is deactivated
        if ($user->is_active != 1) {
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
        
        // Allow access only for roles with exam monitoring permission.
        if (!Gate::forUser($user)->allows('admin.exam.monitor')) {
            if (Gate::forUser($user)->allows('admin.applicants.monitor')) {
                return redirect()->route('applications_list')
                    ->with('error', 'Access denied. HR Division can only access applicants management.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Access denied.');
        }
        
        return $next($request);
    }
}
