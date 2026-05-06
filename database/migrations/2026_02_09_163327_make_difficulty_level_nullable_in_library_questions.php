<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('library_questions') || !Schema::hasColumn('library_questions', 'difficulty_level')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE library_questions MODIFY difficulty_level ENUM('easy','medium','hard') NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE library_questions ALTER COLUMN difficulty_level DROP NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('library_questions') || !Schema::hasColumn('library_questions', 'difficulty_level')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE library_questions SET difficulty_level = 'medium' WHERE difficulty_level IS NULL");
            DB::statement("ALTER TABLE library_questions MODIFY difficulty_level ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("UPDATE library_questions SET difficulty_level = 'medium' WHERE difficulty_level IS NULL");
            DB::statement("ALTER TABLE library_questions ALTER COLUMN difficulty_level SET DEFAULT 'medium'");
            DB::statement("ALTER TABLE library_questions ALTER COLUMN difficulty_level SET NOT NULL");
        }
    }
};
