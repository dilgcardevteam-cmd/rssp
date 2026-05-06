<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->boolean('details_saved')->default(false)->after('notified_at');
            $table->boolean('link_sent')->default(false)->after('details_saved');
            $table->dateTime('link_sent_at')->nullable()->after('link_sent');
        });
    }

    public function down(): void
    {
        Schema::table('exam_details', function (Blueprint $table) {
            $table->dropColumn(['details_saved', 'link_sent', 'link_sent_at']);
        });
    }
};
