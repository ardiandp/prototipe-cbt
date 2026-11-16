<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'level' => fake()->randomElement(['Semua', '10', '11', '12']),
            'group' => fake()->randomElement(['Muatan Nasional', 'Muatan Kewilayahan', 'Muatan Peminatan Kejuruan']),
            'is_active' => true,
        ];
    }
}

