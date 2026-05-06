<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use App\Models\Admin;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class activityLogController extends Controller
{
    public function view(Request $request)
    {
        // Get admin names
        $adminNames = Activity::whereNotNull('causer_id')
            ->with('causer')
            ->get()
            ->filter(fn($act) => $act->causer && $act->causer->name) // skip missing admins
            ->pluck('causer.name')
            ->unique()
            ->sort()
            ->values();


        // Get sections from properties->section instead of log_name
        $sections = Activity::whereNotNull('properties->section')
            ->get()
            ->pluck('properties.section')
            ->unique()
            ->sort()
            ->values();

        // Initial logs shown on load (descending by default)
        $activities = Activity::whereNotIn('event', ['view'])
            ->orderBy('created_at', 'desc')
            ->with('causer', 'subject')
            ->get();

        

        return view('admin.admin_activity_log', compact('activities', 'adminNames', 'sections'));
    }

    public function fetch(Request $request)
    {
        $query = Activity::query()
            ->whereNotIn('event', ['view'])
            ->with('causer', 'subject');

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%$search%");
        }

        if ($adminName = $request->input('admin_name')) {
            $query->whereHas('causer', function ($q) use ($adminName) {
                $q->where('name', 'like', "%$adminName%");
            });
        }

        if ($section = $request->input('section')) {
            $query->where('properties->section', $section);
        }

        if ($range = $request->input('date_range')) {
            $dates = explode(' to ', $range);
            if (count($dates) === 2) {
                [$start, $end] = $dates;
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            }
        }

        // Sorting order: default to DESC
        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->get();

        $logs = $activities->map(function ($activity) {
            $target = 'N/A';
            if ($activity->subject) {
                $target = $activity->subject->name ??
                    $activity->subject->position_title ??
                    $activity->subject->title ??
                    'ID: ' . $activity->subject->id ??
                    'N/A';
            }

            if ($target === 'N/A' && isset($activity->properties['position_title'])) {
                $target = $activity->properties['position_title'];
            }

            return [
                'id' => $activity->id,
                'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
                'admin_name' => optional($activity->causer)->name ?? 'N/A',
                'role' => (function () use ($activity) {
                    if (!$activity->causer) return 'N/A';
                    if ($activity->causer_type === Admin::class) {
                        return $activity->causer->role ?? 'Admin';
                    }
                    return 'User';
                })(),
                'section' => $activity->properties['section'] ?? 'N/A',
                'description' => (function () use ($activity, $target) {
                    $actor = optional($activity->causer)->name ?? 'Someone';
                    $desc = $activity->description ?? '';
                    $event = $activity->event ?? null;
                    $eventText = strtolower(trim((string) $event));
                    $isFailedLogin = in_array($eventText, ['login_failed', 'failed_login', 'auth_failed'], true)
                        || stripos($desc, 'failed login') !== false
                        || stripos($desc, 'logged in unsuccessfully') !== false
                        || (strcasecmp((string) $event, 'login') === 0 && (stripos($desc, 'unsuccess') !== false || stripos($desc, 'failed') !== false));
                    $isLogin = !$isFailedLogin && (strcasecmp((string)$event, 'login') === 0 || stripos($desc, 'logged in') !== false);
                    $isLogout = strcasecmp((string)$event, 'logout') === 0 || stripos($desc, 'logged out') !== false;
                    if ($isFailedLogin) {
                        $email = trim((string) ($activity->properties['email'] ?? ''));
                        if ($email !== '') {
                            return 'Failed login attempt for ' . $email . '.';
                        }
                        return 'Failed login attempt.';
                    }
                    if ($isLogin) {
                        return $actor . ' logged in.';
                    }
                    if ($isLogout) {
                        return $actor . ' logged out.';
                    }
                    $core = $desc ?: 'performed an action';
                    $core = rtrim($core, ". \t\n\r\0\x0B");
                    if (function_exists('lcfirst')) {
                        $core = lcfirst($core);
                    }
                    $section = $activity->properties['section'] ?? null;
                    if ($section === 'System Users Management') {
                        $core = str_ireplace('an admin account', 'the account', $core);
                    }
                    if ($target !== 'N/A') {
                        $core .= ' of ' . $target;
                    }
                    return $actor . ' ' . $core . '.';
                })(),
                'description_html' => (function () use ($activity, $target) {
                    $actor = optional($activity->causer)->name ?? 'Someone';
                    $actorHtml = '<strong>' . htmlspecialchars($actor, ENT_QUOTES, 'UTF-8') . '</strong>';
                    $desc = $activity->description ?? '';
                    $event = $activity->event ?? null;
                    $eventText = strtolower(trim((string) $event));
                    $isFailedLogin = in_array($eventText, ['login_failed', 'failed_login', 'auth_failed'], true)
                        || stripos($desc, 'failed login') !== false
                        || stripos($desc, 'logged in unsuccessfully') !== false
                        || (strcasecmp((string) $event, 'login') === 0 && (stripos($desc, 'unsuccess') !== false || stripos($desc, 'failed') !== false));
                    $isLogin = !$isFailedLogin && (strcasecmp((string)$event, 'login') === 0 || stripos($desc, 'logged in') !== false);
                    $isLogout = strcasecmp((string)$event, 'logout') === 0 || stripos($desc, 'logged out') !== false;
                    if ($isFailedLogin) {
                        $email = trim((string) ($activity->properties['email'] ?? ''));
                        if ($email !== '') {
                            return 'Failed login attempt for <strong>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</strong>.';
                        }
                        return 'Failed login attempt.';
                    }
                    if ($isLogin) {
                        return $actorHtml . ' logged in.';
                    }
                    if ($isLogout) {
                        return $actorHtml . ' logged out.';
                    }
                    $core = $desc ?: 'performed an action';
                    $core = rtrim($core, ". \t\n\r\0\x0B");
                    if (function_exists('lcfirst')) {
                        $core = lcfirst($core);
                    }
                    $section = $activity->properties['section'] ?? null;
                    if ($section === 'System Users Management') {
                        $core = str_ireplace('an admin account', 'the account', $core);
                    }
                    $coreHtml = htmlspecialchars($core, ENT_QUOTES, 'UTF-8');
                    $suffix = '';
                    if ($target !== 'N/A') {
                        $suffix = ' of ' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
                    }
                    return $actorHtml . ' ' . $coreHtml . $suffix . '.';
                })(),
                'vacancy_id' => $activity->properties['vacancy_id'] ?? null,
                'subject' => $activity->subject ? $activity->subject : 'N/A',
                'has_email_preview' => (function () use ($activity) {
                    $ids = $activity->properties['email_log_ids'] ?? null;
                    if (is_array($ids) && ! empty($ids)) {
                        return true;
                    }
                    $event = strtolower(trim((string) ($activity->event ?? '')));
                    $section = $activity->properties['section'] ?? '';
                    if ($event === 'email_sent' || $section === 'Email Logs') {
                        return EmailLog::where('subject', $activity->properties['subject'] ?? '')
                            ->whereBetween('sent_at', [
                                $activity->created_at->clone()->subSeconds(5),
                                $activity->created_at->clone()->addSeconds(5),
                            ])
                            ->exists();
                    }
                    return false;
                })(),
                'email_log_id' => (function () use ($activity) {
                    $ids = $activity->properties['email_log_ids'] ?? null;
                    if (is_array($ids) && ! empty($ids)) {
                        return (int) $ids[0];
                    }
                    $event = strtolower(trim((string) ($activity->event ?? '')));
                    $section = $activity->properties['section'] ?? '';
                    if ($event === 'email_sent' || $section === 'Email Logs') {
                        $log = EmailLog::where('subject', $activity->properties['subject'] ?? '')
                            ->whereBetween('sent_at', [
                                $activity->created_at->clone()->subSeconds(5),
                                $activity->created_at->clone()->addSeconds(5),
                            ])
                            ->first();
                        return $log ? $log->id : null;
                    }
                    return null;
                })(),
            ];
        });

        //info($logs);

        return response()->json($logs);
    }

    public function showEmail(EmailLog $emailLog)
    {
        return response()->json([
            'id' => $emailLog->id,
            'subject' => $emailLog->subject,
            'recipient_email' => $emailLog->recipient_email,
            'sent_at' => $emailLog->sent_at?->format('Y-m-d H:i:s'),
            'body_html' => $emailLog->body_html ?? '<p>No HTML content available.</p>',
        ]);
    }
}
