<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->string('vacancy_id')->nullable()->after('user_id');
            $table->index(['user_id', 'vacancy_id', 'document_type'], 'uploaded_docs_user_vacancy_doc_idx');
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropIndex('uploaded_docs_user_vacancy_doc_idx');
            $table->dropColumn('vacancy_id');
        });
    }
};

