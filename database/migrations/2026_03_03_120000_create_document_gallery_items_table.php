<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type')->nullable();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size_8b')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'document_type'], 'document_gallery_user_doc_type_idx');
            $table->index(['user_id', 'created_at'], 'document_gallery_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_gallery_items');
    }
};

