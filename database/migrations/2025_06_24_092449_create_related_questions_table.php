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
        Schema::create('related_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('rel_third_deg_details')->nullable();
            $table->string('admin_offense_details')->nullable();
            $table->string('criminal_charged_details')->nullable();
            $table->string('convicted_details')->nullable();
            $table->string('separated_details')->nullable();
            $table->string('candidate_details')->nullable();
            $table->string('pwd_details')->nullable();
            $table->string('solo_parent_details')->nullable();
            $table->string('indigenous_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('related_questions');
    }
};
