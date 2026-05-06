<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE admins MODIFY COLUMN role ENUM('superadmin', 'admin', 'hr_division', 'viewer') NOT NULL DEFAULT 'viewer'"
        );
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::table('admins')
            ->where('role', 'hr_division')
            ->update(['role' => 'admin']);

        DB::statement(
            "ALTER TABLE admins MODIFY COLUMN role ENUM('superadmin', 'admin', 'viewer') NOT NULL DEFAULT 'viewer'"
        );
    }
};
