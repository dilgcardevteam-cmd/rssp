<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
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
        'last_modified_by',
        'last_modified_at',
    ];

    protected $casts = [
        'closing_date' => 'datetime',
        'monthly_salary' => 'decimal:2',
        'education_rule_compiled' => 'array',
        'education_rule_compiled_at' => 'datetime',
        'supporting_documents_required' => 'array',
    ];

    /**
     * Boot model events to generate vacancy_id.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->vacancy_id)) {
                $job->vacancy_id = 'TEMP'; // temporary placeholder
            }
        });

        static::created(function ($job) {
            if ($job->vacancy_id === 'TEMP') {
                $acronym = self::generateAcronym($job->position_title);
                $suffix = str_pad($job->id, 3, '0', STR_PAD_LEFT);
                $job->vacancy_id = $acronym . '-' . $suffix;
            } else {
                $suffix = str_pad($job->id, 3, '0', STR_PAD_LEFT);
                $job->vacancy_id = strtok($job->vacancy_id, '-') . '-' . $suffix;
            }
            $job->save();
        });

        static::updating(function ($job) {
            if ($job->isDirty('vacancy_id')) {
                $suffix = str_pad($job->id, 3, '0', STR_PAD_LEFT);
                $job->vacancy_id = strtok($job->vacancy_id, '-') . '-' . $suffix;
            }
        });
    }

    /**
     * Get the position title without (COS N) suffix.
     */
    public function getPositionTitleAttribute($value)
    {
        return preg_replace('/\s*\([^)]*\)\s*$/', '', $value);
    }

    /**
     * Generate acronym based on position title.
     */
    public static function generateAcronym($title)
    {
        $words = preg_split('/\s+/', trim($title));
        if (empty($words)) return '';

        $acronymParts = [];
        $lastIndex = count($words) - 1;

        for ($i = 0; $i < $lastIndex; $i++) {
            $acronymParts[] = strtoupper(substr($words[$i], 0, 1));
        }

        $lastWord = strtoupper($words[$lastIndex]);

        // Check if last word is Roman numeral
        $isRoman = preg_match('/^(M{0,3})(CM|CD|D?C{0,3})?(XC|XL|L?X{0,3})?(IX|IV|V?I{0,3})$/', $lastWord);
        $acronymParts[] = $isRoman ? $lastWord : strtoupper(substr($lastWord, 0, 1));

        return implode('', $acronymParts);
    }

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_ASSESSMENT = 'ASSESSMENT';
    public const STATUS_DELIBERATION = 'DELIBERATION';
    public const STATUS_CONCLUDED = 'CONCLUDED';

    /**
     * Relationship: Applications submitted for this vacancy.
     */
    public function applications()
    {
        return $this->hasMany(Applications::class, 'vacancy_id', 'vacancy_id');
    }

    public function hiredApplications()
    {
        return $this->applications()
            ->where('status', 'Hired')
            ->with('personalInformation');
    }

    public function qualifiedApplications()
    {
        return $this->applications()
            ->where('qs_result', 'Qualified')
            ->with('personalInformation');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function getRouteKeyName()
    {
        return 'vacancy_id'; // or 'pcn_no' or any unique field
    }

    public function examDetail()
    {
        return $this->hasOne(ExamDetail::class, 'vacancy_id', 'vacancy_id');
    }

    public function getProcessStatus()
    {
        $status = strtoupper($this->status);
        if ($status !== self::STATUS_OPEN) {
            return $status;
        }

        $closingDate = \Carbon\Carbon::parse($this->closing_date)->setTime(17, 0, 0);
        $now = \Carbon\Carbon::now();
        
        if ($now->greaterThan($closingDate)) {
            return 'CLOSED';
        }

        $closingSoonThreshold = $closingDate->copy()->subDays(3);
        if ($now->greaterThanOrEqualTo($closingSoonThreshold)) {
            return 'CLOSING_SOON';
        }

        return self::STATUS_OPEN;
    }
}
