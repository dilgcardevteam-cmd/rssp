<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_details') && !Schema::hasColumn('exam_details', 'batch_no')) {
            Schema::table('exam_details', function (Blueprint $table) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            });
            DB::statement('UPDATE exam_details SET batch_no = 1 WHERE batch_no IS NULL');
        }

        if (Schema::hasTable('exam_items') && !Schema::hasColumn('exam_items', 'batch_no')) {
            Schema::table('exam_items', function (Blueprint $table) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            });
            DB::statement('UPDATE exam_items SET batch_no = 1 WHERE batch_no IS NULL');
        }

        if (Schema::hasTable('exam_library_usage') && !Schema::hasColumn('exam_library_usage', 'batch_no')) {
            Schema::table('exam_library_usage', function (Blueprint $table) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            });
            DB::statement('UPDATE exam_library_usage SET batch_no = 1 WHERE batch_no IS NULL');
        }
    }

    public function down(): void
    {
        // Intentionally no destructive rollback for repair migration.
    }
};

