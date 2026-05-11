<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function uniqueIndexExists(string $table, string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admin_notification_preferences')) {
            return;
        }

        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_notification_preferences', 'notification_type')) {
                $table->string('notification_type')->after('admin_id');
            }

            if (!Schema::hasColumn('admin_notification_preferences', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('notification_type');
            }
        });

        if (
            Schema::hasColumn('admin_notification_preferences', 'admin_id')
            && Schema::hasColumn('admin_notification_preferences', 'notification_type')
            && !$this->uniqueIndexExists('admin_notification_preferences', 'admin_pref_unique')
            && !$this->uniqueIndexExists('admin_notification_preferences', 'admin_notification_preferences_admin_id_notification_type_unique')
        ) {
            Schema::table('admin_notification_preferences', function (Blueprint $table) {
                // Add unique constraint to prevent duplicate preferences for same type
                $table->unique(['admin_id', 'notification_type'], 'admin_pref_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('admin_notification_preferences')) {
            return;
        }

        Schema::table('admin_notification_preferences', function (Blueprint $table) {
            if ($this->uniqueIndexExists('admin_notification_preferences', 'admin_pref_unique')) {
                $table->dropUnique('admin_pref_unique');
            }

            if (Schema::hasColumn('admin_notification_preferences', 'notification_type')) {
                $table->dropColumn('notification_type');
            }

            if (Schema::hasColumn('admin_notification_preferences', 'is_enabled')) {
                $table->dropColumn('is_enabled');
            }
        });
    }
};
