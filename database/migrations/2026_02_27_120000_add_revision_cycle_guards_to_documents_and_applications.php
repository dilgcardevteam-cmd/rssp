<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
                $table->unsignedTinyInteger('revision_requested_count')->default(0)->after('status');
            }
            if (!Schema::hasColumn('uploaded_documents', 'revision_requested_at')) {
                $table->timestamp('revision_requested_at')->nullable()->after('revision_requested_count');
            }
            if (!Schema::hasColumn('uploaded_documents', 'revision_submitted_at')) {
                $table->timestamp('revision_submitted_at')->nullable()->after('revision_requested_at');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'file_revision_requested_count')) {
                $table->unsignedTinyInteger('file_revision_requested_count')->default(0)->after('file_status');
            }
            if (!Schema::hasColumn('applications', 'file_revision_requested_at')) {
                $table->timestamp('file_revision_requested_at')->nullable()->after('file_revision_requested_count');
            }
            if (!Schema::hasColumn('applications', 'file_revision_submitted_at')) {
                $table->timestamp('file_revision_submitted_at')->nullable()->after('file_revision_requested_at');
            }
        });

        DB::table('uploaded_documents')
            ->whereRaw('LOWER(TRIM(status)) IN (?, ?)', ['needs revision', 'disapproved with deficiency'])
            ->update([
                'revision_requested_count' => 1,
                'revision_requested_at' => now(),
            ]);

        DB::table('applications')
            ->whereRaw('LOWER(TRIM(file_status)) IN (?, ?)', ['needs revision', 'disapproved with deficiency'])
            ->update([
                'file_revision_requested_count' => 1,
                'file_revision_requested_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            if (Schema::hasColumn('uploaded_documents', 'revision_submitted_at')) {
                $table->dropColumn('revision_submitted_at');
            }
            if (Schema::hasColumn('uploaded_documents', 'revision_requested_at')) {
                $table->dropColumn('revision_requested_at');
            }
            if (Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
                $table->dropColumn('revision_requested_count');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'file_revision_submitted_at')) {
                $table->dropColumn('file_revision_submitted_at');
            }
            if (Schema::hasColumn('applications', 'file_revision_requested_at')) {
                $table->dropColumn('file_revision_requested_at');
            }
            if (Schema::hasColumn('applications', 'file_revision_requested_count')) {
                $table->dropColumn('file_revision_requested_count');
            }
        });
    }
};
