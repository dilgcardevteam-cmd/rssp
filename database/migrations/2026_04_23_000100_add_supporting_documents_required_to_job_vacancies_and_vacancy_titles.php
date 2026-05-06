<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            if (!Schema::hasColumn('job_vacancies', 'supporting_documents_required')) {
                $table->json('supporting_documents_required')->nullable()->after('qualification_eligibility');
            }
        });

        Schema::table('vacancy_titles', function (Blueprint $table) {
            if (!Schema::hasColumn('vacancy_titles', 'supporting_documents_required')) {
                $table->json('supporting_documents_required')->nullable()->after('qualification_eligibility');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            if (Schema::hasColumn('job_vacancies', 'supporting_documents_required')) {
                $table->dropColumn('supporting_documents_required');
            }
        });

        Schema::table('vacancy_titles', function (Blueprint $table) {
            if (Schema::hasColumn('vacancy_titles', 'supporting_documents_required')) {
                $table->dropColumn('supporting_documents_required');
            }
        });
    }
};