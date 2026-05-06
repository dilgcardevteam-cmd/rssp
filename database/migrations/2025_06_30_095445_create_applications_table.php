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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('vacancy_id');
            $table->string('status');
            $table->date('deadline_date')->nullable();
            $table->time('deadline_time')->nullable();
            $table->string('result')->nullable();
            $table->json('answers')->nullable();
            $table->json('scores')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();

            // Application Letter
            $table->string('file_original_name')->nullable();
            $table->string('file_stored_name')->nullable();
            $table->string('file_storage_path')->nullable();
            $table->text('file_remarks')->nullable();
            $table->string('file_status')->nullable();
            $table->unsignedBigInteger('file_size_8b')->nullable();

            // Qualification Standard
            $table->string('qs_education')->nullable();
            $table->string('qs_eligibility')->nullable();
            $table->string('qs_experience')->nullable();
            $table->string('qs_training')->nullable();
            $table->string('qs_result')->nullable();
            $table->text('application_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
