<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('admin_notification_preferences') || Schema::hasColumn('admin_notification_preferences', 'admin_id')) {
            return;
        }

        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->constrained('admins')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('admin_notification_preferences') || !Schema::hasColumn('admin_notification_preferences', 'admin_id')) {
            return;
        }

        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
