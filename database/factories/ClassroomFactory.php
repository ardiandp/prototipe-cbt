<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroom>
 */
class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(['10', '11', '12']);

        return [
            'name' => 'Kelas ' . $level . ' ' . strtoupper(fake()->lexify('???')) . ' ' . fake()->numberBetween(1, 4),
            'level' => $level,
            'department_id' => Department::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'is_active' => true,
        ];
    }
}
