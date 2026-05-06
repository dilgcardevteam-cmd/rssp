<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherInformation extends Model
{
    /** @use HasFactory<\Database\Factories\OtherInformationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill',
        'distinction',
        'organization',
    ];
    
    protected $casts = [
        'skill' => 'array',
        'distinction' => 'array',
        'organization' => 'array',
    ];

    /**
     * Defines a one-to-many relationship with a User and a Other Information record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, OtherInformation>
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
