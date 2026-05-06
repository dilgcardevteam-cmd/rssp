<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    private function applyViewerExamFilter($query)
    {
        return $query->where(function ($examQuery) {
            $examQuery->where('data->category', 'exam_lifecycle')
                ->orWhere('data->section', 'Exam Management')
                ->orWhere('data->title', 'Exam Management')
                ->orWhere('data->link', 'like', '%/admin/exam_management/%')
                ->orWhere('data->action_url', 'like', '%/admin/exam_management/%');
        });
    }

    private function getQuery()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $query = Notification::where('notifiable_type', 'App\Models\Admin')
                ->where(function ($q) {
                    $q->where('notifiable_id', Auth::guard('admin')->id())
                        ->orWhereNull('notifiable_id');
                });

            if (strtolower((string) ($admin->role ?? '')) === 'viewer') {
                $query = $this->applyViewerExamFilter($query);
            }

            return $query;
        } elseif (Auth::guard('web')->check()) {
            return Notification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', Auth::id());
        }
        return null;
    }

    // Fetch count of unread notifications
    public function unreadCount()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['count' => 0, 'display' => '0']);

        $count = $query
            ->whereNull('read_at')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $display = $count >= 100 ? '99+' : (string) $count;
        return response()->json(['count' => $count, 'display' => $display]);
    }

    // Index method
    public function index(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.notifications.index');
        }

        $query = $this->getQuery();
        if (!$query) {
            return view('notifications.index', ['notifications' => collect(), 'filter' => 'all', 'unreadCount' => 0]);
        }

        $filter = $request->query('filter', 'all');
        $listQuery = clone $query;
        if ($filter === 'unread') {
            $listQuery->whereNull('read_at');
        }

        $notifications = $listQuery->latest()->paginate(20)->withQueryString();
        $unreadCount = $query->whereNull('read_at')->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter'        => $filter,
            'unreadCount'   => $unreadCount,
        ]);
    }

    public function adminIndex(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('notifications.index');
        }

        $query = $this->getQuery();
        if (!$query) {
            return view('notifications.admin_index', ['notifications' => collect(), 'filter' => 'all', 'unreadCount' => 0]);
        }

        $filter = $request->query('filter', 'all');
        $listQuery = clone $query;
        if ($filter === 'unread') {
            $listQuery->whereNull('read_at');
        }

        $notifications = $listQuery->latest()->paginate(20)->withQueryString();
        $unreadCount = $query->whereNull('read_at')->count();

        return view('notifications.admin_index', [
            'notifications' => $notifications,
            'filter'        => $filter,
            'unreadCount'   => $unreadCount,
        ]);
    }

    // Fetch latest notifications (last 24 hours only)
    public function fetch()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['notifications' => []]);

        $notifications = $query
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->paginate(10);

        // Return standard notifications collection
        $payload = $notifications->toArray();
        $payload['notifications'] = $payload['data'] ?? [];
        return response()->json($payload);
    }

    // Mark all as read
    public function markAll()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['success' => false], 403);

        $query->whereNull('read_at')->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Mark individual notification as read
    public function markAsRead($id)
    {
        $query = $this->getQuery();
        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification = $query->whereKey($id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if ($notification->read_at) {
            return response()->json(['success' => true]);
        }

        $notification->forceFill([
            'read_at' => now(),
            'updated_at' => now(),
        ])->save();

        return response()->json(['success' => true]);
    }

    // Clear all notifications
    public function cleanup()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['success' => false], 403);

        $query->delete();

        return response()->json(['success' => true]);
    }
}
