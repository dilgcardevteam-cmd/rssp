<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('program_suggestions')) {
            return;
        }

        Schema::create('program_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suggested_by_user_id')->nullable()->index();
            $table->string('program_level', 20)->default('COLLEGE')->index();
            $table->string('suggested_name');
            $table->string('normalized_name')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('source', 50)->default('pds_c1');
            $table->unsignedBigInteger('course_preset_id')->nullable()->index();
            $table->unsignedBigInteger('reviewed_by_admin_id')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['program_level', 'normalized_name', 'status'], 'program_suggestions_level_name_status_unique');
            $table->foreign('suggested_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('course_preset_id')->references('id')->on('course_presets')->nullOnDelete();
            $table->foreign('reviewed_by_admin_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_suggestions');
    }
};
