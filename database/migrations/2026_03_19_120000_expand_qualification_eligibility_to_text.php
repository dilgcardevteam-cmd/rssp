<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE job_vacancies MODIFY qualification_eligibility TEXT NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE job_vacancies ALTER COLUMN qualification_eligibility TYPE TEXT");
            DB::statement("ALTER TABLE job_vacancies ALTER COLUMN qualification_eligibility SET NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE job_vacancies MODIFY qualification_eligibility VARCHAR(255) NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE job_vacancies ALTER COLUMN qualification_eligibility TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE job_vacancies ALTER COLUMN qualification_eligibility SET NOT NULL");
        }
    }
};
