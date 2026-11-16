<?php

namespace Database\Factories;

use App\Models\QuestionCategory;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionCategory>
 */
class QuestionCategoryFactory extends Factory
{
    protected $model = QuestionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('BNK-???-###'));

        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'code' => $code,
            'name' => 'Bank Soal ' . fake()->words(2, true),
            'level' => fake()->randomElement(['Semua', '10', '11', '12']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
