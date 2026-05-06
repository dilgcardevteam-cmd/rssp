<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_presets')) {
            return;
        }

        if (!Schema::hasColumn('course_presets', 'program_level')) {
            Schema::table('course_presets', function (Blueprint $table) {
                $table->string('program_level', 20)->default('COLLEGE')->index();
            });
        }

        DB::table('course_presets')
            ->whereNull('program_level')
            ->orWhere('program_level', '')
            ->update(['program_level' => 'COLLEGE']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('course_presets')) {
            return;
        }

        if (Schema::hasColumn('course_presets', 'program_level')) {
            Schema::table('course_presets', function (Blueprint $table) {
                $table->dropIndex(['program_level']);
                $table->dropColumn('program_level');
            });
        }
    }
};

