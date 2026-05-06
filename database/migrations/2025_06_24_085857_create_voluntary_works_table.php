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
        Schema::create('voluntary_works', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('voluntary_org');
            $table->date('voluntary_from');
            $table->date('voluntary_to');
            $table->smallInteger('voluntary_hours');
            $table->string('voluntary_position');

            $table->unique(['voluntary_org', 'voluntary_from', 'user_id'], 'unique_voluntary_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voluntary_works');
    }
};
