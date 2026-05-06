<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_vacancy_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('vacancy_id');
            $table->timestamps();

            $table->unique(['admin_id', 'vacancy_id'], 'admin_vacancy_access_unique');
            $table->index('vacancy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_vacancy_accesses');
    }
};
