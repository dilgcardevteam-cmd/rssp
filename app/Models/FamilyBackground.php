<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyBackground extends Model
{
    /** @use HasFactory<\Database\Factories\FamilyBackgroundFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        
        'spouse_surname', 
        'spouse_first_name', 
        'spouse_name_extension',
        'spouse_middle_name',
        'spouse_occupation', 
        'spouse_employer', 
        'spouse_business_address',
        'spouse_telephone', 
        
        'father_surname', 
        'father_first_name', 
        'father_middle_name', 
        'father_name_extension',

        'mother_maiden_surname',
        'mother_maiden_first_name', 
        'mother_maiden_middle_name',

        'children_info'
    ];

    protected $casts = [
        'children_info' => 'array'
    ];
    
    /**
     * Defines a one-to-one relationship with a User and a Family Background record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, FamilyBackground>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
