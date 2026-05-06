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
        Schema::create('library_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->text('question');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'essay', 'short_answer'])->default('multiple_choice');
            $table->json('choices')->nullable(); // For multiple choice and true/false
            $table->string('correct_answer')->nullable(); // For multiple choice, true/false, short answer
            $table->text('essay_answer_guide')->nullable(); // For essay questions
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable(); // Array of tags for filtering
            $table->timestamps();

            $table->foreign('series_id')->references('id')->on('question_series')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_questions');
    }
};
