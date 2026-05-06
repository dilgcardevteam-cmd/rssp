<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatedQuestions extends Model
{
    /** @use HasFactory<\Database\Factories\RelatedQuestionsFactory> */
    use HasFactory;

    /**
     * Defines a one-to-one relationship with a User and a Related Questions record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, RelatedQuestions>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
