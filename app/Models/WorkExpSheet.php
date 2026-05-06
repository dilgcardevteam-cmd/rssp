<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExpSheet extends Model
{
    /** @use HasFactory<\Database\Factories\WorkExpSheetFactory> */
    use HasFactory;

    protected $table = 'work_exp_sheet';

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'position',
        'office',
        'supervisor',
        'agency',
        'accomplishments',
        'duties',
        'isDisplayed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'accomplishments' => 'array',
        'duties' => 'array',
    ];

    /**
     * Relationship to User (optional)
     */

     public function setEndDateAttribute($value)
    {
        // Check if value is today; if so, store as null
        if ($value === now()->format('Y-m-d')) {
            $this->attributes['end_date'] = null;
        } else {
            $this->attributes['end_date'] = $value;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
