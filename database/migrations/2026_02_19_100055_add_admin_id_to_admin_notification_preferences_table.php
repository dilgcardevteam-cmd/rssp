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
        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->constrained('admins')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
