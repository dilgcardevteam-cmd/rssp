<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'batch_no',
        'is_started',
        'time',
        'time_end',
        'date',
        'place',
        'message',
        'duration',
        'max_violations',
        'notified_at',
        'details_saved',
        'link_sent',
        'link_sent_at',
        'exam_paused_at',
        'exam_paused_by_admin_id',
        'exam_pause_seconds',
    ];

    public function vacancy()
    {

        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }
}
