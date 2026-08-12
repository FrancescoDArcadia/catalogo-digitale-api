<?php

namespace Database\Factories;

use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;
use App\Models\Category;

/**
 * @extends Factory<Work>
 */
class WorkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'publication_year' => fake()->year(),
            'author_id' => Author::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
