<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (Schema::hasColumn('users', 'middle_initial') && !Schema::hasColumn('users', 'middle_name')) {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE users RENAME COLUMN middle_initial TO middle_name');
                return;
            }
            DB::statement('ALTER TABLE users CHANGE middle_initial middle_name VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (Schema::hasColumn('users', 'middle_name') && !Schema::hasColumn('users', 'middle_initial')) {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE users RENAME COLUMN middle_name TO middle_initial');
                return;
            }
            DB::statement('ALTER TABLE users CHANGE middle_name middle_initial VARCHAR(255) NULL');
        }
    }
};
