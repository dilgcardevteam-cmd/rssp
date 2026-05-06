<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiscInfos extends Model
{
    /** @use HasFactory<\Database\Factories\MiscInfosFactory> */
    use HasFactory;

    protected $casts = [ 'criminal_35_b' => 'array'];

    //protected $guarded = [];

    protected $fillable = [
            'user_id',              
            'related_34_a',
            'related_34_b',
            'guilty_35_a',        
            'criminal_35_b', 
            'convicted_36',
            'separated_37',
            'candidate_38',
            'resigned_38_b',
            'immigrant_39',
            'indigenous_40_a',
            'pwd_40_b',                         
            'solo_parent_40_c',

            'ref1_name',
            'ref1_tel',
            'ref1_address',
            'ref2_name',
            'ref2_tel',
            'ref2_address',
            'ref3_name',
            'ref3_tel',
            'ref3_address',
            
            'govt_id_type',
            'govt_id_number',
            'govt_id_date_issued',
            'govt_id_place_issued',
            
            'photo_upload',
            'declaration',
            'consent',

    ]; 

    

    /**
     * Defines a one-to-one relationship with a User and a Miscellaneous Information record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, MiscInfos>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
