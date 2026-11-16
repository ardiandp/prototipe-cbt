<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionOption>
 */
class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'option_text' => fake()->sentence(),
            'option_image' => null,
            'is_correct' => false,
            'order' => fake()->numberBetween(1, 5),
        ];
    }
}
