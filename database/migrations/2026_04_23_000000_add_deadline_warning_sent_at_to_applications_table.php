<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'deadline_warning_sent_at')) {
                $table->timestamp('deadline_warning_sent_at')->nullable()->after('deadline_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'deadline_warning_sent_at')) {
                $table->dropColumn('deadline_warning_sent_at');
            }
        });
    }
};