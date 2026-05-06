<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('role');
            }
            if (!Schema::hasColumn('admins', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('admins', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('admins', 'declined_at')) {
                $table->timestamp('declined_at')->nullable()->after('approved_at');
            }
        });

        DB::table('admins')->whereNull('approval_status')->update(['approval_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'declined_at')) {
                $table->dropColumn('declined_at');
            }
            if (Schema::hasColumn('admins', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('admins', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('admins', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
