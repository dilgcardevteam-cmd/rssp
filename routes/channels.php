<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('exam-monitor.{vacancyId}', function ($admin, $vacancyId) {
    return Gate::forUser($admin)->allows('admin.exam.monitor')
        || Gate::forUser($admin)->allows('admin.exam.manage');
}, ['guards' => ['admin']]);

Broadcast::channel('exam-participant.{vacancyId}.{userId}', function ($actor, $vacancyId, $userId) {
    if ($actor instanceof \App\Models\User) {
        return (int) $actor->id === (int) $userId;
    }

    if ($actor instanceof \App\Models\Admin) {
        return Gate::forUser($actor)->allows('admin.exam.monitor')
            || Gate::forUser($actor)->allows('admin.exam.manage');
    }

    return false;
}, ['guards' => ['web', 'admin']]);
