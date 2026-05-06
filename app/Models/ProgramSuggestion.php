<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramSuggestion extends Model
{
    use HasFactory;

    protected $table = 'program_suggestions';

    protected $fillable = [
        'suggested_by_user_id',
        'program_level',
        'suggested_name',
        'normalized_name',
        'status',
        'source',
        'course_preset_id',
        'reviewed_by_admin_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function suggestedBy()
    {
        return $this->belongsTo(User::class, 'suggested_by_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function approvedCourse()
    {
        return $this->belongsTo(CoursePreset::class, 'course_preset_id');
    }
}
