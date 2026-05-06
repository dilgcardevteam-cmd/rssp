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
        // test
        Schema::create('personal_information', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
            $table->string('cs_id_no')->nullable();
            $table->string('surname')->default('NOINPUT');
            $table->string('name_extension')->nullable();
            $table->string('first_name')->default('NOINPUT');
            $table->string('middle_name')->nullable();
            $table->string('sex')->default('NOINPUT');
            $table->string('civil_status')->default('NOINPUT');
            $table->date('date_of_birth')->default(DB::raw('(CURRENT_DATE)'));
            $table->string('place_of_birth')->default('NOINPUT');
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->char('blood_type')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('agency_employee_no')->nullable();
            $table->string('gsis_id_no')->nullable();
            $table->string('pagibig_id_no')->nullable();
            $table->string('sss_id_no')->nullable();
            $table->string('citizenship')->default('NOINPUT');
            $table->string('dual_country')->nullable(); // changed from dual_citizenship_country to current...
            $table->string('dual_type')->nullable(); // changed from by_birth_or_by_natural to dual_type...
            $table->string('residential_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('email_address')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_information');
    }
};
