<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->timestamp('exam_paused_at')->nullable()->after('is_started');
            $table->unsignedBigInteger('exam_paused_by_admin_id')->nullable()->after('exam_paused_at');
            $table->unsignedInteger('exam_pause_seconds')->default(0)->after('exam_paused_by_admin_id');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('exam_paused_at')->nullable()->after('exam_end_time');
            $table->unsignedBigInteger('exam_paused_by_admin_id')->nullable()->after('exam_paused_at');
            $table->unsignedInteger('exam_pause_seconds')->default(0)->after('exam_paused_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->dropColumn(['exam_paused_at', 'exam_paused_by_admin_id', 'exam_pause_seconds']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['exam_paused_at', 'exam_paused_by_admin_id', 'exam_pause_seconds']);
        });
    }
};
