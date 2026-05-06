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
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('exam_token_used_at')->nullable()->after('exam_token_expires_at');
            $table->string('exam_token_device_id', 64)->nullable()->after('exam_token_used_at');
            $table->string('exam_token_used_ip', 45)->nullable()->after('exam_token_device_id');
            $table->text('exam_token_used_ua')->nullable()->after('exam_token_used_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'exam_token_used_at',
                'exam_token_device_id',
                'exam_token_used_ip',
                'exam_token_used_ua',
            ]);
        });
    }
};

