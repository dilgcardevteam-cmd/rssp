<?php

namespace App\Http\Middleware;

use App\Models\JobVacancy;
use App\Services\ApplicantDeletionWorkflowService;
use App\Services\ApplicantRevisionDeadlineService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RunDailyTask
{
    public function handle($request, Closure $next)
    {
        $lastRun = Cache::get('daily_task_last_run');

        if (!$lastRun || Carbon::parse($lastRun)->lt(Carbon::today())) {
            JobVacancy::where('closing_date', '<', Carbon::now())
                ->where('status', 'OPEN')
                ->update(['status' => 'CLOSED']);

            $workflow = app(ApplicantDeletionWorkflowService::class);
            $deletionResults = $workflow->processDailyTasks();
            $deadlineService = app(ApplicantRevisionDeadlineService::class);
            $deadlineResults = $deadlineService->processDailyTasks();

            if (!empty($deletionResults['deleted']) && $request->hasSession() && Auth::guard('admin')->check()) {
                $request->session()->flash(
                    'applicant_deletion_daily_notice',
                    'Scheduled deletion completed for ' . $workflow->summarizeLabels($deletionResults['deleted']) . '.'
                );
            }

            if (!empty($deadlineResults['expired']) && $request->hasSession() && Auth::guard('admin')->check()) {
                $request->session()->flash(
                    'applicant_revision_deadline_notice',
                    'Revision deadline expired for ' . implode(', ', array_slice($deadlineResults['expired'], 0, 3)) . (count($deadlineResults['expired']) > 3 ? ' and others' : '') . '.'
                );
            }

            Cache::put('daily_task_last_run', Carbon::now());

            info('RunDailyTask!');
        }

        return $next($request);
    }
}
