<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    /** @use HasFactory<\Database\Factories\EducationalBackgroundFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',

        'elem_school', 
        'elem_from', 
        'elem_to', 
        'elem_academic_honors',
        'elem_basic', 
        'elem_earned', 
        'elem_year_graduated',

        'jhs_from',
        'jhs_to',
        'jhs_school',
        'jhs_academic_honors',
        'jhs_basic',
        'jhs_earned',
        'jhs_year_graduated',
        
        'shs_from',
        'shs_to',
        'shs_school',
        'shs_academic_honors',
        'shs_basic',
        'shs_earned',
        'shs_year_graduated',

        'vocational',
        'college',
        'grad'
        ];

    protected $casts = [
        'vocational' => 'array',
        'college' => 'array',
        'grad' => 'array',
    ];

    /**
     * Defines a one-to-one relationship with a User and a Educational Background record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, EducationalBackground>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
