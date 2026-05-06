<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $table = 'job_vacancies';

    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'created_by_admin_id',
        'position_title',
        'vacancy_type',
        'pcn_no',
        'plantilla_item_no',
        'closing_date',
        'status',
        'monthly_salary',
        'salary_grade',
        'place_of_assignment',

        'qualification_education',
        'qualification_training',
        'qualification_experience',
        'qualification_eligibility',

        'competencies',

        'expected_output',
        'scope_of_work',
        'duration_of_work',

        'to_person',
        'to_position',
        'to_office',
        'to_office_address',

        'last_modified_by',
        'last_modified_at',
    ];


}

