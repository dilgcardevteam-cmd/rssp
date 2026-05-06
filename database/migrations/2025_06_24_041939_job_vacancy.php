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
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('vacancy_id')->unique();
            $table->string('position_title');
            $table->string('vacancy_type');

            // Plantilla-specific fields
            $table->string('pcn_no')->nullable();
            $table->string('plantilla_item_no')->nullable();

            // Salary and assignment
            $table->decimal('monthly_salary', 10, 2);
            $table->string('salary_grade')->nullable();
            $table->string('place_of_assignment');

            // Status and closing
            $table->string('status'); // OPEN or CLOSED
            $table->dateTime('closing_date');

            // Qualification standards
            $table->string('qualification_education');
            $table->string('qualification_training');
            $table->string('qualification_experience');
            $table->string('qualification_eligibility');

            // Competencies (Plantilla)
            $table->text('competencies')->nullable();

            // COS deliverables
            $table->text('expected_output')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('duration_of_work')->nullable();

            // Submission details
            $table->string('to_person');
            $table->string('to_position');
            $table->string('to_office');
            $table->string('to_office_address');

            // Modification metadata
            $table->string('last_modified_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
