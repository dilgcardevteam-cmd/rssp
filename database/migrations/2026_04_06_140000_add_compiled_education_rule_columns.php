<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addColumnIfMissing = function (string $table, string $column, callable $callback): void {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
                return;
            }

            Schema::table($table, $callback);
        };

        foreach (['job_vacancies', 'vacancy_titles'] as $table) {
            $addColumnIfMissing($table, 'education_rule_compiled', function (Blueprint $blueprint) {
                $blueprint->longText('education_rule_compiled')->nullable()->after('qualification_education');
            });
            $addColumnIfMissing($table, 'education_rule_parser_version', function (Blueprint $blueprint) {
                $blueprint->unsignedSmallInteger('education_rule_parser_version')->nullable()->after('education_rule_compiled');
            });
            $addColumnIfMissing($table, 'education_rule_compiled_at', function (Blueprint $blueprint) {
                $blueprint->timestamp('education_rule_compiled_at')->nullable()->after('education_rule_parser_version');
            });
        }

        $addColumnIfMissing('applications', 'education_requirement_snapshot', function (Blueprint $blueprint) {
            $blueprint->text('education_requirement_snapshot')->nullable()->after('qs_education');
        });
        $addColumnIfMissing('applications', 'education_rule_snapshot', function (Blueprint $blueprint) {
            $blueprint->longText('education_rule_snapshot')->nullable()->after('education_requirement_snapshot');
        });
        $addColumnIfMissing('applications', 'education_rule_snapshot_version', function (Blueprint $blueprint) {
            $blueprint->unsignedSmallInteger('education_rule_snapshot_version')->nullable()->after('education_rule_snapshot');
        });
    }

    public function down(): void
    {
        $dropColumnIfExists = function (string $table, string $column): void {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                return;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });
        };

        foreach (['job_vacancies', 'vacancy_titles'] as $table) {
            $dropColumnIfExists($table, 'education_rule_compiled_at');
            $dropColumnIfExists($table, 'education_rule_parser_version');
            $dropColumnIfExists($table, 'education_rule_compiled');
        }

        $dropColumnIfExists('applications', 'education_rule_snapshot_version');
        $dropColumnIfExists('applications', 'education_rule_snapshot');
        $dropColumnIfExists('applications', 'education_requirement_snapshot');
    }
};

