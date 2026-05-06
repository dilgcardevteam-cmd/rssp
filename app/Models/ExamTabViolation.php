<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamTabViolation extends Model
{
    use HasFactory;

    protected $table = 'exam_tab_violations';

    protected $fillable = [
        'user_id',
        'vacancy_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'duration_milliseconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
