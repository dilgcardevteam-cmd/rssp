<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    /** @use HasFactory<\Database\Factories\PersonalInformationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cs_id_no',
        'surname',
        'first_name',
        'middle_name',
        'name_extension',
        'date_of_birth',
        'place_of_birth',
        'sex',
        'civil_status',
        'height',
        'weight',
        'blood_type',
        'philhealth_no',
        'tin_no',
        'agency_employee_no',
        'gsis_id_no',
        'pagibig_id_no',
        'sss_id_no',
        'citizenship',
        'dual_country',
        'dual_type',
        'residential_address',
        'permanent_address',
        'telephone_no',
        'mobile_no',
        'email_address',
    ];

    /**
     * Defines a one-to-one relationship with a User and a Personal Information record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, PersonalInformation>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
