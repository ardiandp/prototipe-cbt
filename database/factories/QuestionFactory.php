<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_category_id' => QuestionCategory::factory(),
            'teacher_id' => Teacher::factory(),
            'code' => 'QST-' . fake()->unique()->numerify('####'),
            'question_text' => fake()->paragraph() . ' Manakah jawaban yang paling tepat?',
            'question_image' => null,
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'weight' => fake()->randomElement([1.00, 2.00, 2.50, 4.00]),
            'status' => 'active',
            'order' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * Configure the factory to generate options A-E with one correct answer.
     */
    public function withOptions(string $correctLabel = 'A'): static
    {
        return $this->afterCreating(function (Question $question) use ($correctLabel) {
            $labels = ['A', 'B', 'C', 'D', 'E'];
            foreach ($labels as $index => $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'option_text' => fake()->sentence(),
                    'is_correct' => ($label === strtoupper($correctLabel)),
                    'order' => $index + 1,
                ]);
            }
        });
    }
}
