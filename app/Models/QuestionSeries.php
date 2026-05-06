<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_name',
        'description',
        'created_by',
    ];

    /**
     * Get the admin who created this series
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get all questions in this series
     */
    public function questions()
    {
        return $this->hasMany(LibraryQuestion::class, 'series_id');
    }

    /**
     * Get the count of questions in this series
     */
    public function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }
}
