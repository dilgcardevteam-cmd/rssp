<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applications extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'updated_by_admin_id',
        'vacancy_id',
        'status',
        'result',
        'answers',
        'scores',
        'is_valid',
        'deadline_date',
        'deadline_time',
        'file_original_name',
        'file_stored_name',
        'file_storage_path',
        'file_remarks',
        'file_status',
        'file_revision_requested_count',
        'file_revision_requested_at',
        'file_revision_submitted_at',
        'deadline_warning_sent_at',
        'file_size_8b',
        'qs_education',
        'education_requirement_snapshot',
        'education_rule_snapshot',
        'education_rule_snapshot_version',
        'initial_assessment_degree',
        'initial_assessment_eligibility',
        'initial_assessment_q1_passed',
        'initial_assessment_q2_passed',
        'initial_assessment_has_pqe',
        'qs_eligibility',
        'qs_experience',
        'qs_training',
        'qs_result',
        'application_remarks',
        'link_sent_at',
        'exam_token',
        'exam_token_expires_at',
        'exam_token_used_at',
        'exam_token_device_id',
        'exam_token_used_ip',
        'exam_token_used_ua',
        'read_at',
        'exam_attendance_status',
        'exam_attendance_remark',
        'exam_attendance_responded_at',
        'exam_started_at',
        'exam_end_time',
        'exam_submitted_at',
        'exam_paused_at',
        'exam_paused_by_admin_id',
        'exam_pause_seconds',
        'tab_violations',
        'last_tab_violation_at'
    ];

    protected $casts = [
        'answers' => 'array',
        'scores' => 'array',
        'education_rule_snapshot' => 'array',
        'initial_assessment_q1_passed' => 'boolean',
        'initial_assessment_q2_passed' => 'boolean',
        'initial_assessment_has_pqe' => 'boolean',
        'deadline_warning_sent_at' => 'datetime',
        'exam_attendance_responded_at' => 'datetime',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = ApplicationStatus::normalize(is_null($value) ? null : (string) $value);
    }

    public function scopeStatusEquals($query, string $status)
    {
        $normalized = ApplicationStatus::normalize($status);
        if ($normalized === null || $normalized === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('LOWER(TRIM(status)) = ?', [strtolower($normalized)]);
    }

    public function scopeStatusIn($query, array $statuses)
    {
        $normalized = collect($statuses)
            ->map(fn($status) => ApplicationStatus::normalize(is_null($status) ? null : (string) $status))
            ->filter(fn($status) => !is_null($status) && $status !== '')
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $placeholders = implode(',', array_fill(0, $normalized->count(), '?'));
        $lowerValues = $normalized->map(fn($status) => strtolower((string) $status))->all();

        return $query->whereRaw("LOWER(TRIM(status)) IN ({$placeholders})", $lowerValues);
    }

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function updatedByAdmin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'updated_by_admin_id');
    }

    

}
