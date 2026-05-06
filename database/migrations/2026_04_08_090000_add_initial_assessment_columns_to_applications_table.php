<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'initial_assessment_degree')) {
                $table->string('initial_assessment_degree')->nullable()->after('education_rule_snapshot_version');
            }

            if (!Schema::hasColumn('applications', 'initial_assessment_eligibility')) {
                $table->string('initial_assessment_eligibility')->nullable()->after('initial_assessment_degree');
            }

            if (!Schema::hasColumn('applications', 'initial_assessment_q1_passed')) {
                $table->boolean('initial_assessment_q1_passed')->nullable()->after('initial_assessment_eligibility');
            }

            if (!Schema::hasColumn('applications', 'initial_assessment_q2_passed')) {
                $table->boolean('initial_assessment_q2_passed')->nullable()->after('initial_assessment_q1_passed');
            }

            if (!Schema::hasColumn('applications', 'initial_assessment_has_pqe')) {
                $table->boolean('initial_assessment_has_pqe')->nullable()->after('initial_assessment_q2_passed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'initial_assessment_has_pqe')) {
                $table->dropColumn('initial_assessment_has_pqe');
            }

            if (Schema::hasColumn('applications', 'initial_assessment_q2_passed')) {
                $table->dropColumn('initial_assessment_q2_passed');
            }

            if (Schema::hasColumn('applications', 'initial_assessment_q1_passed')) {
                $table->dropColumn('initial_assessment_q1_passed');
            }

            if (Schema::hasColumn('applications', 'initial_assessment_eligibility')) {
                $table->dropColumn('initial_assessment_eligibility');
            }

            if (Schema::hasColumn('applications', 'initial_assessment_degree')) {
                $table->dropColumn('initial_assessment_degree');
            }
        });
    }
};
