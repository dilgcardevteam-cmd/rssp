<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    /** @use HasFactory<\Database\Factories\WorkExperienceFactory> */
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'work_exp_from',
        'work_exp_to',
        'work_exp_position',
        'work_exp_department',
        'work_exp_status',
        'work_exp_govt_service'
    ];
    /**
     * Defines a one-to-many relationship with a User and a Work Experience record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, WorkExperience>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
