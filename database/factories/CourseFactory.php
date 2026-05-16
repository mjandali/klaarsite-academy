<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = fake()->sentence(4);
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'thumbnail_url' => fake()->imageUrl(),
            'price' => fake()->numberBetween(10, 500),
            'currency' => 'USD',
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_hours' => fake()->numberBetween(5, 100),
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'meta_description' => fake()->sentence(),
            'course_format' => fake()->randomElement(['text', 'video', 'mixed']),
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the course should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the course should be archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
