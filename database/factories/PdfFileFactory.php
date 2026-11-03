<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\PdfFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PdfFile>
 */
class PdfFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'title' => fake()->sentence(3),
            'path' => 'pdfs/'.fake()->uuid().'.pdf',
        ];
    }
}
