<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_presets')) {
            Schema::create('course_presets', function (Blueprint $table) {
                $table->id();
                $table->string('course_code', 120)->unique();
                $table->string('course_name')->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('course_presets') && DB::table('course_presets')->count() === 0) {
            DB::table('course_presets')->insert([
                ['course_code' => 'BS_ACCOUNTANCY', 'course_name' => 'BS Accountancy', 'created_at' => now(), 'updated_at' => now()],
                ['course_code' => 'BS_INFORMATION_TECHNOLOGY', 'course_name' => 'BS Information Technology', 'created_at' => now(), 'updated_at' => now()],
                ['course_code' => 'BS_COMPUTER_SCIENCE', 'course_name' => 'BS Computer Science', 'created_at' => now(), 'updated_at' => now()],
                ['course_code' => 'BS_INFORMATION_SYSTEMS', 'course_name' => 'BS Information Systems', 'created_at' => now(), 'updated_at' => now()],
                ['course_code' => 'B_PUBLIC_ADMIN', 'course_name' => 'Bachelor of Public Administration', 'created_at' => now(), 'updated_at' => now()],
                ['course_code' => 'BS_PSYCHOLOGY', 'course_name' => 'BS Psychology', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_presets');
    }
};

