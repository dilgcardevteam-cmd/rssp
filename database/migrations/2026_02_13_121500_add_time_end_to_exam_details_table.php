<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->time('time_end')->nullable()->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->dropColumn('time_end');
        });
    }
};
