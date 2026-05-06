<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'designation',
        'office',
        'office_address',
    ];
}
