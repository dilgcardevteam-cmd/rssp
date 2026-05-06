<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deletion_requested_by_applicant_at')) {
                $table->timestamp('deletion_requested_by_applicant_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'deletion_request_received_by_admin_at')) {
                $table->timestamp('deletion_request_received_by_admin_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deletion_request_received_by_admin_at')) {
                $table->dropColumn('deletion_request_received_by_admin_at');
            }

            if (Schema::hasColumn('users', 'deletion_requested_by_applicant_at')) {
                $table->dropColumn('deletion_requested_by_applicant_at');
            }
        });
    }
};
