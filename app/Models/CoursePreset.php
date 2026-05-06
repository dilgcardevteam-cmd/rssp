<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePreset extends Model
{
    use HasFactory;

    protected $table = 'course_presets';

    protected $fillable = [
        'course_code',
        'course_name',
        'program_level',
    ];
}
