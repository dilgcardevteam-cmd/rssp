<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_details', function (Blueprint $table) {
            $table->id();
            $table->string('vacancy_id'); // adjust nullable if needed
            $table->boolean('is_started')->default(0);

            $table->time('time')->nullable();
            $table->date('date')->nullable();
            $table->string('place')->nullable();
            $table->integer('duration')->nullable();
            $table->dateTime('notified_at')->nullable();
            $table->timestamps();

            // If vacancy_id references job_vacancies table, add foreign key constraint:
            // $table->foreign('vacancy_id')->references('id')->on('job_vacancies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_details');
    }
};
