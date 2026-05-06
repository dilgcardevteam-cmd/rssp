<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['vacancy_id', 'status', 'created_at'], 'applications_vacancy_status_created_idx');
            $table->index(['user_id', 'vacancy_id'], 'applications_user_vacancy_idx');
        });

        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'job_vacancies_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_vacancy_status_created_idx');
            $table->dropIndex('applications_user_vacancy_idx');
        });

        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropIndex('job_vacancies_status_created_idx');
        });
    }
};

