<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => CourseStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CourseStatus::Published,
        ]);
    }
}
