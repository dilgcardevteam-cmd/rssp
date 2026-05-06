<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('application_exam_attempts')) {
            Schema::create('application_exam_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('user_id');
                $table->string('vacancy_id');
                $table->unsignedTinyInteger('batch_no');
                $table->string('status')->default('pending');
                $table->string('result')->nullable();
                $table->json('answers')->nullable();
                $table->json('scores')->nullable();
                $table->timestamp('exam_started_at')->nullable();
                $table->timestamp('exam_end_time')->nullable();
                $table->timestamp('exam_submitted_at')->nullable();
                $table->timestamp('exam_paused_at')->nullable();
                $table->unsignedBigInteger('exam_paused_by_admin_id')->nullable();
                $table->integer('exam_pause_seconds')->default(0);
                $table->integer('tab_violations')->default(0);
                $table->timestamp('last_tab_violation_at')->nullable();
                $table->timestamps();

                $table->unique(['application_id', 'batch_no'], 'attempt_unique_per_batch');
                $table->index(['vacancy_id', 'batch_no']);
                $table->index(['user_id', 'vacancy_id', 'batch_no']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally keep data safe; no destructive rollback for this repair migration.
    }
};

