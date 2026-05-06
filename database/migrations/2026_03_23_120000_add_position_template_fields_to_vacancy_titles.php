<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $addColumnIfMissing = function (string $column, callable $callback): void {
            if (!Schema::hasColumn('vacancy_titles', $column)) {
                Schema::table('vacancy_titles', $callback);
            }
        };

        $addColumnIfMissing('vacancy_type', fn (Blueprint $table) => $table->string('vacancy_type', 20)->nullable()->after('position_title'));
        $addColumnIfMissing('pcn_no', fn (Blueprint $table) => $table->string('pcn_no')->nullable()->after('vacancy_type'));
        $addColumnIfMissing('plantilla_item_no', fn (Blueprint $table) => $table->string('plantilla_item_no')->nullable()->after('pcn_no'));
        $addColumnIfMissing('closing_date', fn (Blueprint $table) => $table->date('closing_date')->nullable()->after('plantilla_item_no'));
        $addColumnIfMissing('place_of_assignment', fn (Blueprint $table) => $table->string('place_of_assignment')->nullable()->after('monthly_salary'));
        $addColumnIfMissing('qualification_education', fn (Blueprint $table) => $table->text('qualification_education')->nullable()->after('place_of_assignment'));
        $addColumnIfMissing('qualification_training', fn (Blueprint $table) => $table->text('qualification_training')->nullable()->after('qualification_education'));
        $addColumnIfMissing('qualification_experience', fn (Blueprint $table) => $table->text('qualification_experience')->nullable()->after('qualification_training'));
        $addColumnIfMissing('qualification_eligibility', fn (Blueprint $table) => $table->text('qualification_eligibility')->nullable()->after('qualification_experience'));
        $addColumnIfMissing('competencies', fn (Blueprint $table) => $table->text('competencies')->nullable()->after('qualification_eligibility'));
        $addColumnIfMissing('expected_output', fn (Blueprint $table) => $table->text('expected_output')->nullable()->after('competencies'));
        $addColumnIfMissing('scope_of_work', fn (Blueprint $table) => $table->text('scope_of_work')->nullable()->after('expected_output'));
        $addColumnIfMissing('duration_of_work', fn (Blueprint $table) => $table->text('duration_of_work')->nullable()->after('scope_of_work'));
        $addColumnIfMissing('to_person', fn (Blueprint $table) => $table->string('to_person')->nullable()->after('duration_of_work'));
        $addColumnIfMissing('to_position', fn (Blueprint $table) => $table->string('to_position')->nullable()->after('to_person'));
        $addColumnIfMissing('to_office', fn (Blueprint $table) => $table->string('to_office')->nullable()->after('to_position'));
        $addColumnIfMissing('to_office_address', fn (Blueprint $table) => $table->string('to_office_address')->nullable()->after('to_office'));
        $addColumnIfMissing('csc_form_path', fn (Blueprint $table) => $table->string('csc_form_path')->nullable()->after('to_office_address'));
    }

    public function down(): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $dropColumnIfExists = function (string $column): void {
            if (Schema::hasColumn('vacancy_titles', $column)) {
                Schema::table('vacancy_titles', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        };

        foreach ([
            'csc_form_path',
            'to_office_address',
            'to_office',
            'to_position',
            'to_person',
            'duration_of_work',
            'scope_of_work',
            'expected_output',
            'competencies',
            'qualification_eligibility',
            'qualification_experience',
            'qualification_training',
            'qualification_education',
            'place_of_assignment',
            'closing_date',
            'plantilla_item_no',
            'pcn_no',
            'vacancy_type',
        ] as $column) {
            $dropColumnIfExists($column);
        }
    }
};

