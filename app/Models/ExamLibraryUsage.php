<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamLibraryUsage extends Model
{
    use HasFactory;

    protected $table = 'exam_library_usage';

    protected $fillable = [
        'vacancy_id',
        'batch_no',
        'library_question_id',
        'order',
    ];

    /**
     * Get the library question
     */
    public function libraryQuestion()
    {
        return $this->belongsTo(LibraryQuestion::class, 'library_question_id');
    }

    /**
     * Get the vacancy/exam
     */
    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }
}
