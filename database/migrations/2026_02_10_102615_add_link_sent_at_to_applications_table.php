<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('link_sent_at')->nullable()->after('status');
            $table->string('exam_token', 64)->nullable()->after('link_sent_at');
            $table->timestamp('exam_token_expires_at')->nullable()->after('exam_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['link_sent_at', 'exam_token', 'exam_token_expires_at']);
        });
    }
};
