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
        Schema::create('misc_infos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('related_34_a', 3)->nullable();
            $table->string('related_34_b')->nullable();
            $table->string('guilty_35_a')->nullable();
            $table->json('criminal_35_b')->nullable();
            $table->string('convicted_36')->nullable();
            $table->string('separated_37')->nullable();
            $table->string('candidate_38')->nullable();
            $table->string('resigned_38_b')->nullable();
            $table->string('immigrant_39')->nullable();
            $table->string('indigenous_40_a')->nullable();
            $table->string('pwd_40_b')->nullable();
            $table->string('solo_parent_40_c')->nullable();

            $table->string('ref1_name')->nullable();
            $table->string('ref1_tel')->nullable();
            $table->string('ref1_address')->nullable();
            $table->string('ref2_name')->nullable();
            $table->string('ref2_tel')->nullable();
            $table->string('ref2_address')->nullable();
            $table->string('ref3_name')->nullable();
            $table->string('ref3_tel')->nullable();
            $table->string('ref3_address')->nullable();
            
            $table->string('govt_id_type')->nullable();
            $table->string('govt_id_number')->nullable();
            $table->string('govt_id_date_issued')->nullable();
            $table->string('govt_id_place_issued')->nullable();

            $table->string('photo_upload')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('misc_infos');
    }
};
