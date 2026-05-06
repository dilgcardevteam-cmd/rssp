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
        Schema::create('work_exp_sheet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->string('office');
            $table->string('supervisor');
            $table->string('agency');
            $table->json('accomplishments')->nullable();
            $table->json('duties')->nullable();
            $table->boolean('isDisplayed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_exp_sheet');
    }
};
