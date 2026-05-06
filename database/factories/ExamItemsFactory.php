<?php

namespace Database\Factories;

use App\Models\ExamItems;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamItemsFactory extends Factory
{
    protected $model = ExamItems::class;

    public function definition(): array
    {
        return [
            'vacancy_id' => $this->faker->numberBetween(1, 10), // adjust based on your vacancies table

            'question' => $this->faker->sentence(),

            'is_essay' => $this->faker->boolean(),

            'choices' => [
                'A' => $this->faker->word(),
                'B' => $this->faker->word(),
                'C' => $this->faker->word(),
                'D' => $this->faker->word(),
            ],

            'ans' => $this->faker->randomElement(['A', 'B', 'C', 'D']),

        ];
    }
}
