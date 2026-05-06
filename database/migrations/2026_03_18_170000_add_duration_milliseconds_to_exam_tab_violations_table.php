<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_tab_violations')) {
            return;
        }

        Schema::table('exam_tab_violations', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_tab_violations', 'duration_milliseconds')) {
                $table->unsignedInteger('duration_milliseconds')->nullable()->after('duration_seconds');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_tab_violations') || !Schema::hasColumn('exam_tab_violations', 'duration_milliseconds')) {
            return;
        }

        Schema::table('exam_tab_violations', function (Blueprint $table) {
            $table->dropColumn('duration_milliseconds');
        });
    }
};
