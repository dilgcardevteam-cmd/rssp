<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->string('last_modified_by')->nullable()->after('status');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('file_last_modified_by')->nullable()->after('file_status');
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropColumn('last_modified_by');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('file_last_modified_by');
        });
    }
};
