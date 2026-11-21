<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'type' => fake()->randomElement(['anime', 'drama', 'manga', 'novel']),
            'cover_url' => fake()->optional()->imageUrl(),
            'synopsis' => fake()->paragraph(),
            'episodes' => fake()->optional()->numberBetween(1, 24),
            'year' => fake()->numberBetween(1990, 2026),
            'score' => fake()->randomFloat(1, 5, 10),
            'genres' => fake()->randomElements(['Action', 'Drama', 'Romance', 'Fantasy'], 2),
        ];
    }
}
