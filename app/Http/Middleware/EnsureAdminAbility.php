<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();
        if (!Gate::forUser($admin)->allows($ability)) {
            $message = $this->messageForAbility($ability);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            $role = (string) ($admin->role ?? '');
            $redirectRoute = match ($role) {
                'viewer' => 'viewer',
                'hr_division' => 'applications_list',
                default => 'dashboard_admin',
            };

            return redirect()->route($redirectRoute)->with('error', $message);
        }

        return $next($request);
    }

    private function messageForAbility(string $ability): string
    {
        return match ($ability) {
            'admin.exam.monitor' => 'Access denied. You can only monitor ongoing exams.',
            'admin.exam.manage' => 'Access denied. You cannot modify, score, or notify for exams.',
            'admin.applicants.monitor' => 'Access denied. You cannot access applicant management.',
            'admin.system.manage' => 'Access denied. Superadmin privileges are required.',
            default => 'Access denied.',
        };
    }
}

