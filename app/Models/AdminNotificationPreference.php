<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'notification_type',
        'is_enabled',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
