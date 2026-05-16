<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Support\VideoEmbedParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            [
                'title' => 'Laravel for Beginners',
                'subtitle' => 'Learn Laravel from scratch',
                'description' => '<h2>Build real Laravel skills</h2><p>Start from the fundamentals and move toward building clean, structured features in a real application.</p><pre><code>Route::get(\'/courses\', fn () => \'Ready\');</code></pre>',
                'price' => 49.99,
                'level' => 'beginner',
                'duration_hours' => 20,
                'course_format' => 'mixed',
                'status' => 'published',
            ],
            [
                'title' => 'Vue 3 Fundamentals',
                'subtitle' => 'Vue 3 from basics to advanced',
                'description' => '<h2>Interactive frontends</h2><p>Learn how to build modern Vue 3 interfaces with composition patterns, routing, and reusable components.</p>',
                'price' => 0,
                'level' => 'beginner',
                'duration_hours' => 15,
                'course_format' => 'video',
                'status' => 'published',
            ],
            [
                'title' => 'Advanced PHP',
                'subtitle' => 'Master advanced PHP concepts',
                'description' => '<h2>Professional PHP patterns</h2><p>Explore architecture, refactoring, and maintainable PHP workflows for production applications.</p>',
                'price' => 59.99,
                'level' => 'intermediate',
                'duration_hours' => 25,
                'course_format' => 'text',
                'status' => 'published',
            ],
        ];

        foreach ($courses as $courseData) {
            $course = Course::create([
                'user_id' => 1,
                'title' => $courseData['title'],
                'subtitle' => $courseData['subtitle'],
                'description' => $courseData['description'],
                'price' => $courseData['price'],
                'level' => $courseData['level'],
                'duration_hours' => $courseData['duration_hours'],
                'course_format' => $courseData['course_format'],
                'status' => $courseData['status'],
                'published_at' => $courseData['status'] === 'published' ? now() : null,
                'slug' => Str::slug($courseData['title']),
            ]);

            $sections = [
                ['title' => 'Introduction', 'order' => 1],
                ['title' => 'Fundamentals', 'order' => 2],
                ['title' => 'Advanced Topics', 'order' => 3],
            ];

            foreach ($sections as $sectionData) {
                $section = CourseSection::create([
                    'course_id' => $course->id,
                    'title' => $sectionData['title'],
                    'description' => 'Guided lessons for '.$sectionData['title'],
                    'order' => $sectionData['order'],
                ]);

                for ($i = 1; $i <= 3; $i++) {
                    $lessonType = match ($courseData['course_format']) {
                        'text' => 'text',
                        'video' => 'video',
                        default => $i === 1 ? 'text' : 'mixed',
                    };

                    $video = $lessonType === 'text'
                        ? ['video_url' => null, 'video_provider' => null, 'video_id' => null]
                        : VideoEmbedParser::normalize('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

                    Lesson::create([
                        'course_section_id' => $section->id,
                        'title' => "{$sectionData['title']} - Lesson $i",
                        'description' => "<p>This lesson walks through {$sectionData['title']} with practical, production-minded examples.</p>",
                        'type' => $lessonType,
                        'content' => '<p>Lesson content goes here with a safe subset of HTML.</p><pre><code>// Example snippet for the lesson</code></pre>',
                        'video_url' => $video['video_url'],
                        'video_provider' => $video['video_provider'],
                        'video_id' => $video['video_id'],
                        'duration_minutes' => 15 * $i,
                        'order' => $i,
                        'status' => 'published',
                    ]);
                }
            }
        }
    }
}
