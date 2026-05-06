<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CivilServiceEligibility extends Model
{
    /** @use HasFactory<\Database\Factories\CivilServiceEligibilityFactory> */
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'cs_eligibility_career',
        'cs_eligibility_rating',
        'cs_eligibility_date',
        'cs_eligibility_place',
        'cs_eligibility_license',
        'cs_eligibility_validity',
    ];

    /**
     * Defines a one-to-many relationship with a User and a Civil Service Eligibility record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, CivilServiceEligibility>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
