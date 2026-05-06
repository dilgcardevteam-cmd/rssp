<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JobVacancyFactory extends Factory
{
    public function definition(): array
    {
        // Determine job type randomly: COS or Plantilla
        $type = $this->faker->randomElement(['COS', 'Plantilla']);

        return [
            'vacancy_id' => 'VAC-' . $this->faker->unique()->randomNumber(5, true),
            'vacancy_type' => $type,
            'position_title' => $this->faker->jobTitle,
            'place_of_assignment' => $this->faker->company . ', ' . $this->faker->city,
            'salary_grade' => $type === 'Plantilla' ? 'SG-' . $this->faker->numberBetween(1, 30) : null,
            'monthly_salary' => $this->faker->randomFloat(2, 11270.0, 60000),
            'status' => $this->faker->randomElement(['OPEN', 'CLOSED']),
            'closing_date' => $this->faker->dateTimeBetween('+5 days', '+2 months'),
            'qualification_education' => 'Bachelor’s degree relevant to the job',
            'qualification_training' => 'At least 8 hours of relevant training',
            'qualification_experience' => '1 year of relevant experience',
            'qualification_eligibility' => 'Career Service Professional / Second Level Eligibility',

            // COS-specific fields
            'expected_output' => $type === 'COS' ? $this->faker->sentence(6) : null,
            'scope_of_work' => $type === 'COS' ? $this->faker->paragraph(2) : null,
            'duration_of_work' => $type === 'COS' ? $this->faker->randomElement(['3 months', '6 months', '1 year']) : null,

            // Plantilla-specific fields
            'pcn_no' => $type === 'Plantilla' ? 'PCN-' . $this->faker->unique()->randomNumber(4, true) : null,
            'plantilla_item_no' => $type === 'Plantilla' ? 'PI-' . $this->faker->unique()->randomNumber(4, true) : null,
            'competencies' => $type === 'Plantilla' ? json_encode([$this->faker->word, $this->faker->word]) : null,

            // Shared/optional fields
            'to_person' => $this->faker->name,
            'to_position' => $this->faker->jobTitle,
            'to_office' => $this->faker->company,
            'to_office_address' => $this->faker->address,
            'last_modified_by' => 'admin1',
        ];
    }
}
