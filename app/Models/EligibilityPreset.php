<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityPreset extends Model
{
    use HasFactory;

    protected $table = 'eligibility_presets';

    protected $fillable = [
        'name',
        'legal_basis',
        'level',
    ];
}

