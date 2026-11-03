<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(2),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
