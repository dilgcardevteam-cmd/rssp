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
        Schema::create('exam_items', function (Blueprint $table) {
            $table->id();
            $table->string('vacancy_id');

            $table->text('question');
            $table->boolean('is_essay')->default(false);

            $table->json('choices')->nullable(); // stores array of choices

            $table->string('ans')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_items');
    }
};
