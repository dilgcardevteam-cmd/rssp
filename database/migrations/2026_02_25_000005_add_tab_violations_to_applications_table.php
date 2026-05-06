<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedInteger('tab_violations')->default(0)->after('exam_submitted_at');
            $table->timestamp('last_tab_violation_at')->nullable()->after('tab_violations');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['tab_violations', 'last_tab_violation_at']);
        });
    }
};

