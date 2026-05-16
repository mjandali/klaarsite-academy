<?php

namespace Database\Factories;

use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'course_section_id' => CourseSection::factory(),
            'title' => fake()->sentence(4),
            'description' => '<p>'.fake()->sentence().'</p>',
            'type' => 'text',
            'content' => '<p>'.fake()->paragraph().'</p>',
            'video_url' => null,
            'video_provider' => null,
            'video_id' => null,
            'duration_minutes' => fake()->numberBetween(5, 60),
            'order' => fake()->numberBetween(1, 10),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
