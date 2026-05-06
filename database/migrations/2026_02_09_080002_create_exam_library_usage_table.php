<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_library_usage', function (Blueprint $table) {
            $table->id();
            $table->string('vacancy_id'); // Which exam is using this question
            $table->unsignedBigInteger('library_question_id'); // Reference to library question
            $table->integer('order')->default(0); // Order in the exam
            $table->timestamps();

            $table->foreign('library_question_id')->references('id')->on('library_questions')->onDelete('cascade');
            $table->unique(['vacancy_id', 'library_question_id']); // Prevent duplicate usage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_library_usage');
    }
};
