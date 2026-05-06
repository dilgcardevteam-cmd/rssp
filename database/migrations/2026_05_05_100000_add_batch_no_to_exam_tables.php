<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_details', 'batch_no')) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            }
        });

        Schema::table('exam_items', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_items', 'batch_no')) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            }
        });

        Schema::table('exam_library_usage', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_library_usage', 'batch_no')) {
                $table->unsignedTinyInteger('batch_no')->default(1)->after('vacancy_id');
            }
        });

        DB::statement('UPDATE exam_details SET batch_no = 1 WHERE batch_no IS NULL');
        DB::statement('UPDATE exam_items SET batch_no = 1 WHERE batch_no IS NULL');
        DB::statement('UPDATE exam_library_usage SET batch_no = 1 WHERE batch_no IS NULL');

        Schema::table('exam_details', function (Blueprint $table) {
            $table->unique(['vacancy_id', 'batch_no'], 'exam_details_vacancy_batch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->dropUnique('exam_details_vacancy_batch_unique');
        });

        Schema::table('exam_library_usage', function (Blueprint $table) {
            if (Schema::hasColumn('exam_library_usage', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });

        Schema::table('exam_items', function (Blueprint $table) {
            if (Schema::hasColumn('exam_items', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });

        Schema::table('exam_details', function (Blueprint $table) {
            if (Schema::hasColumn('exam_details', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });
    }
};

