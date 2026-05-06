<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupAutomationSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'frequency',
        'weekly_day',
        'run_time',
        'recipient_emails',
        'last_run_at',
        'last_status',
        'last_error',
        'last_backup_path',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'weekly_day' => 'integer',
            'recipient_emails' => 'array',
            'last_run_at' => 'datetime',
        ];
    }
}
