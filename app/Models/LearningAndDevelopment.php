<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningAndDevelopment extends Model
{
    /** @use HasFactory<\Database\Factories\LearningAndDevelopmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_title',
        'learning_type',
        'learning_from',
        'learning_to',
        'learning_hours',
        'learning_conducted',

    ];

    /**
     * Defines a one-to-many relationship with a User and a Learning and Development record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LearningAndDevelopment>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
