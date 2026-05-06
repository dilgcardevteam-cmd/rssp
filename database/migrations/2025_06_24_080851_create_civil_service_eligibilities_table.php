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
        Schema::create('civil_service_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('cs_eligibility_career')->default('NOINPUT');
            $table->string('cs_eligibility_rating')->default('NOINPUT');
            $table->date('cs_eligibility_date')->default(DB::raw('(CURRENT_DATE)'));
            $table->string('cs_eligibility_place')->default('NOINPUT');
            $table->string('cs_eligibility_license')->nullable();
            $table->string('cs_eligibility_validity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('civil_service_eligibilities');
    }
};
