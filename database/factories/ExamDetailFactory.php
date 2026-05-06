<?php

namespace Database\Factories;

use App\Models\ExamDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamDetailFactory extends Factory
{
    protected $model = ExamDetail::class;

    public function definition(): array
    {
        return [
            'vacancy_id' => $this->faker->numberBetween(1, 10), // adjust based on your vacancies table

            'time' => $this->faker->time('H:i:s'),

            'date' => $this->faker->date(),

            'place' => $this->faker->city(),

            'duration' => $this->faker->numberBetween(5-10),
        ];
    }
}
