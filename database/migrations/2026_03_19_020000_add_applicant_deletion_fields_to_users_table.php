<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('pending_deletion_at')->nullable()->after('applicant_code');
            $table->timestamp('deletion_due_at')->nullable()->after('pending_deletion_at');
            $table->timestamp('deletion_warning_sent_at')->nullable()->after('deletion_due_at');
            $table->foreignId('deletion_requested_by_admin_id')
                ->nullable()
                ->after('deletion_warning_sent_at')
                ->constrained('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deletion_requested_by_admin_id');
            $table->dropColumn([
                'pending_deletion_at',
                'deletion_due_at',
                'deletion_warning_sent_at',
            ]);
        });
    }
};
