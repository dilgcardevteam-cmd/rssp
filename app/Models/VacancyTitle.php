<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyTitle extends Model
{
    use HasFactory;

    protected $table = 'vacancy_titles';

    protected $fillable = [
        'position_title',
        'vacancy_type',
        'pcn_no',
        'plantilla_item_no',
        'closing_date',
        'salary_grade',
        'monthly_salary',
        'place_of_assignment',
        'qualification_education',
        'education_rule_compiled',
        'education_rule_parser_version',
        'education_rule_compiled_at',
        'qualification_training',
        'qualification_experience',
        'qualification_eligibility',
        'supporting_documents_required',
        'competencies',
        'expected_output',
        'scope_of_work',
        'duration_of_work',
        'to_person',
        'to_position',
        'to_office',
        'to_office_address',
        'csc_form_path',
    ];

    protected $casts = [
        'education_rule_compiled' => 'array',
        'education_rule_compiled_at' => 'datetime',
        'supporting_documents_required' => 'array',
    ];
}
