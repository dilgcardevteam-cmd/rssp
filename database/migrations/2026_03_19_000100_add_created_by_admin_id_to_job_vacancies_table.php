<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            if (!Schema::hasColumn('job_vacancies', 'created_by_admin_id')) {
                $table->unsignedBigInteger('created_by_admin_id')->nullable()->after('id');
                $table->index('created_by_admin_id', 'job_vacancies_created_by_admin_idx');
                $table->foreign('created_by_admin_id', 'job_vacancies_created_by_admin_fk')
                    ->references('id')
                    ->on('admins')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            if (Schema::hasColumn('job_vacancies', 'created_by_admin_id')) {
                $table->dropForeign('job_vacancies_created_by_admin_fk');
                $table->dropIndex('job_vacancies_created_by_admin_idx');
                $table->dropColumn('created_by_admin_id');
            }
        });
    }
};
