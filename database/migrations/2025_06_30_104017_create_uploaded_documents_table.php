<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadedDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('uploaded_documents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('document_type')->default('NOINPUT');
            $table->string('original_name')->default('NOINPUT');
            $table->string('stored_name')->default('NOINPUT');
            $table->string('storage_path')->default('NOINPUT');
            $table->string('mime_type')->default('NOINPUT');
            $table->string('remarks')->default('');
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('file_size_8b')->default(0);
            $table->boolean('isApproved')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploaded_documents');
    }
}
