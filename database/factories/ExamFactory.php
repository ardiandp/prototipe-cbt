<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now()->addHours(2);
        $end = (clone $start)->addHours(4);

        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'code' => 'UJN-' . strtoupper(fake()->bothify('???-###')),
            'title' => 'Asesmen ' . fake()->words(3, true),
            'description' => fake()->paragraph(),
            'start_at' => $start,
            'end_at' => $end,
            'duration' => 90,
            'passing_grade' => 75.00,
            'total_questions' => 0,
            'random_question' => false,
            'random_answer' => false,
            'token' => null,
            'status' => 'draft',
            'is_results_published' => false,
        ];
    }

    /**
     * Indicate that the exam is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the exam is currently ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
        ]);
    }
}
