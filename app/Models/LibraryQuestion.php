<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'question',
        'question_type',
        'choices',
        'correct_answer',
        'essay_answer_guide',
        'essay_max_score',
        'difficulty_level',
        'category',
        'tags',
    ];

    protected $casts = [
        'choices' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the series this question belongs to
     */
    public function series()
    {
        return $this->belongsTo(QuestionSeries::class, 'series_id');
    }

    /**
     * Get all exams using this question
     */
    public function examUsages()
    {
        return $this->hasMany(ExamLibraryUsage::class, 'library_question_id');
    }

    /**
     * Check if this question is used in any exam
     */
    public function isUsedInExams()
    {
        return $this->examUsages()->exists();
    }

    /**
     * Get count of exams using this question
     */
    public function getUsageCountAttribute()
    {
        return $this->examUsages()->count();
    }
}
