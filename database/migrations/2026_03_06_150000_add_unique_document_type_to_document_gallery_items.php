<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_gallery_items')) {
            return;
        }

        $duplicateGroups = DB::table('document_gallery_items')
            ->select('user_id', 'document_type', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('document_type')
            ->groupBy('user_id', 'document_type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $keepId = DB::table('document_gallery_items')
                ->where('user_id', (int) $group->user_id)
                ->where('document_type', (string) $group->document_type)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('id');

            if ($keepId === null) {
                continue;
            }

            DB::table('document_gallery_items')
                ->where('user_id', (int) $group->user_id)
                ->where('document_type', (string) $group->document_type)
                ->where('id', '!=', (int) $keepId)
                ->delete();
        }

        Schema::table('document_gallery_items', function (Blueprint $table) {
            $table->unique(['user_id', 'document_type'], 'document_gallery_user_doc_type_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('document_gallery_items')) {
            return;
        }

        Schema::table('document_gallery_items', function (Blueprint $table) {
            $table->dropUnique('document_gallery_user_doc_type_unique');
        });
    }
};
