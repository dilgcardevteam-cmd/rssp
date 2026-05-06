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
        Schema::create('learning_and_developments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('learning_title')->default("NOINPUT");
            $table->string('learning_type')->default("NOINPUT");
            $table->date('learning_from')->default('2025-06-01');
            $table->date('learning_to')->default('2025-06-02');
            $table->smallInteger('learning_hours')->default(24);
            $table->string('learning_conducted')->default("NOINPUT");

            $table->unique(['id', 'learning_from', 'user_id'], 'unique_learning_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_and_developments');
    }
};
