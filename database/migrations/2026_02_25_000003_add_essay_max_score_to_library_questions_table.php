<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('library_questions', function (Blueprint $table) {
            $table->integer('essay_max_score')->nullable()->after('essay_answer_guide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_questions', function (Blueprint $table) {
            $table->dropColumn('essay_max_score');
        });
    }
};

