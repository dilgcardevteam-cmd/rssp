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
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->date('work_exp_from')->default(DB::raw('(CURRENT_DATE)'));
            $table->date('work_exp_to')->default(DB::raw('(CURRENT_DATE)'));
            $table->string('work_exp_position')->default('NOINPUT');
            $table->string('work_exp_department')->default('NOINPUT');
            $table->string('work_exp_salary')->default('NOINPUT');
            $table->string('work_exp_grade')->default('NOINPUT');
            $table->string('work_exp_status')->default('NOINPUT');
            $table->char('work_exp_govt_service')->default('~');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
