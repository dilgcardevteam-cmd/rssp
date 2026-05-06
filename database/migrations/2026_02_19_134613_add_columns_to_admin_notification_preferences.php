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
        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->string('notification_type')->after('admin_id');
            $table->boolean('is_enabled')->default(true)->after('notification_type');
            
            // Add unique constraint to prevent duplicate preferences for same type
            $table->unique(['admin_id', 'notification_type'], 'admin_pref_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->dropUnique('admin_pref_unique');
            $table->dropColumn(['notification_type', 'is_enabled']);
        });
    }
};
