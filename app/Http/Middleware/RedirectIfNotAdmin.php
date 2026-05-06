<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RedirectIfNotAdmin
{
    public function handle($request, Closure $next)
    {
        // Check if user is not authenticated with admin guard
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
        
        // Check if user has full admin-level access for admin-only routes.
        if (!Gate::forUser($user)->allows('admin.backoffice.full')) {
            if (Gate::forUser($user)->allows('admin.exam.monitor')) {
                return redirect()->route('viewer')
                    ->with('error', 'Access denied. Viewer can only access exam management.');
            }

            if (Gate::forUser($user)->allows('admin.applicants.monitor')) {
                $routeName = $request->route()?->getName();
                $hrDivisionAllowedRoutes = [
                    'home_admin',
                    'dashboard_admin',
                    'admin.account.settings',
                    'admin.account.settings.update',
                    'admin.account.password.update',
                    'vacancies_management',
                    'admin.vacancies.filter',
                    'addcos',
                    'vacancies.addcos',
                    'vacancies.store',
                    'vacancies.edit',
                    'vacancies.update',
                    'vacancies.delete',
                    'admin.positions.index',
                    'admin.positions.list',
                ];

                if (in_array($routeName, $hrDivisionAllowedRoutes, true)) {
                    return $next($request);
                }

                return redirect()->route('applications_list')
                    ->with('error', 'Access denied. HR Division can access dashboard, vacancies, positions, and applicants management only.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Access denied.');
        }
        
        return $next($request);
    }
}
