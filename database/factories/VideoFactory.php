<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'title' => fake()->sentence(3),
            'path' => 'videos/'.fake()->uuid().'.mp4',
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
