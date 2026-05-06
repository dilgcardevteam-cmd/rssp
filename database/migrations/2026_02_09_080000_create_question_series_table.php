<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_series', function (Blueprint $table) {
            $table->id();
            $table->string('series_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by'); // admin_id
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_series');
    }
};
