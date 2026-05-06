<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'username',
        'name',
        'office',
        'designation',
        'email',
        'password',
        'role',
        'is_active',
        'approval_status',
        'approved_by',
        'approved_at',
        'declined_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function preferences()
    {
        return $this->hasMany(AdminNotificationPreference::class);
    }

    public function vacancyAccesses()
    {
        return $this->hasMany(AdminVacancyAccess::class);
    }

    public function wantsNotification($type)
    {
        // Default to true if no preference is set
        $preference = $this->preferences()->where('notification_type', $type)->first();
        return $preference ? $preference->is_enabled : true;
    }
}
