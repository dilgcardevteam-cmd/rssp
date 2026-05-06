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
        Schema::create('educational_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('elem_from', 7)->nullable();
            $table->string('elem_to', 7)->nullable();
            $table->string('elem_school')->nullable();
            $table->string('elem_academic_honors')->nullable();
            $table->string('elem_basic')->nullable();
            $table->string('elem_earned')->nullable();
            $table->string('elem_year_graduated', 4)->nullable();

            $table->string('jhs_from', 7)->nullable();
            $table->string('jhs_to', 7)->nullable();
            $table->string('jhs_school')->nullable();
            $table->string('jhs_academic_honors')->nullable();
            $table->string('jhs_basic')->nullable();
            $table->string('jhs_earned')->nullable();
            $table->string('jhs_year_graduated', 4)->nullable();

            $table->string('shs_from', 7)->nullable();
            $table->string('shs_to', 7)->nullable();
            $table->string('shs_school')->nullable();
            $table->string('shs_academic_honors')->nullable();
            $table->string('shs_basic')->nullable();
            $table->string('shs_earned')->nullable();
            $table->string('shs_year_graduated', 4)->nullable();
            
            $table->json('vocational')->nullable();
            
            $table->json('college')->nullable();
            
            $table->json('grad')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_backgrounds');
    }
};
