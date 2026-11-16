<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        $name = $gender === 'L'
            ? fake()->firstNameMale() . ' ' . fake()->lastName()
            : fake()->firstNameFemale() . ' ' . fake()->lastName();

        return [
            'user_id' => User::factory()->student(),
            'classroom_id' => Classroom::factory(),
            'nis' => (string) fake()->unique()->numberBetween(20261000, 20269999),
            'nisn' => '008' . fake()->unique()->numerify('#######'),
            'name' => $name,
            'gender' => $gender,
            'birth_date' => fake()->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'phone' => '08' . fake()->numerify('##########'),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
