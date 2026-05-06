<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoluntaryWork extends Model
{
    /** @use HasFactory<\Database\Factories\VoluntaryWorkFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voluntary_org',
        'voluntary_from',
        'voluntary_to',
        'voluntary_hours',
        'voluntary_position',

    ];

    /**
     * Defines a one-to-many relationship with a User and a Voluntary Work record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, VoluntaryWork>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
