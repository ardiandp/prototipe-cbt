<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        $name = $gender === 'L'
            ? fake()->firstNameMale() . ' ' . fake()->lastName() . ', S.Pd.'
            : fake()->firstNameFemale() . ' ' . fake()->lastName() . ', M.Pd.';

        return [
            'user_id' => User::factory()->teacher(),
            'nip' => '19' . fake()->numberBetween(70, 99) . fake()->numerify('##########'),
            'name' => $name,
            'gender' => $gender,
            'phone' => '08' . fake()->numerify('##########'),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}

