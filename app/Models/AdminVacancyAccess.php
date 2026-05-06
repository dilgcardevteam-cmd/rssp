<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminVacancyAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'vacancy_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }
}
