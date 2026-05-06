<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'vacancy_id',
        'batch_no',
        'status',
        'result',
        'answers',
        'scores',
        'exam_started_at',
        'exam_end_time',
        'exam_submitted_at',
        'exam_paused_at',
        'exam_paused_by_admin_id',
        'exam_pause_seconds',
        'tab_violations',
        'last_tab_violation_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'scores' => 'array',
        'exam_started_at' => 'datetime',
        'exam_end_time' => 'datetime',
        'exam_submitted_at' => 'datetime',
        'exam_paused_at' => 'datetime',
        'last_tab_violation_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Applications::class, 'application_id');
    }
}

