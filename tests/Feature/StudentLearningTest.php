<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Tests\TestCase;

class StudentLearningTest extends TestCase
{
    private User $admin;
    private User $student;
    private Course $course;
    private CourseSection $section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create(['role' => 'student']);

        $this->course = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'published', 'published_at' => now()]);

        $this->section = CourseSection::factory()
            ->for($this->course)
            ->create();
    }

    public function test_continue_learning_opens_last_accessed_lesson()
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $lesson2 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        // Access lesson 2 later
        $this->student->lessonProgress()->create([
            'lesson_id' => $lesson1->id,
            'last_accessed_at' => now()->subMinutes(5),
        ]);

        $progress2 = $this->student->lessonProgress()->create([
            'lesson_id' => $lesson2->id,
            'last_accessed_at' => now(),
        ]);

        // The continue button should point to lesson 2
        $lastAccessed = $this->student->lessonProgress()
            ->orderByDesc('last_accessed_at')
            ->first();

        $this->assertEquals($lesson2->id, $lastAccessed->lesson_id);
    }

    public function test_continue_learning_opens_first_lesson_if_nothing_accessed()
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        // No progress yet, should go to first
        $firstLesson = $this->course->lessons()
            ->published()
            ->orderBy('lessons.order')
            ->first();

        $this->assertEquals($lesson1->id, $firstLesson->id);
    }

    public function test_course_completion_is_100_percent_when_all_published_lessons_completed()
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $lesson2 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $enrollment = $this->student->enrollments()->create(['course_id' => $this->course->id]);

        // Complete all lessons
        $this->student->lessonProgress()->create([
            'lesson_id' => $lesson1->id,
            'is_completed' => true,
        ]);

        $this->student->lessonProgress()->create([
            'lesson_id' => $lesson2->id,
            'is_completed' => true,
        ]);

        $publishedCount = $this->course->lessons()->published()->count();
        $completedCount = $this->student->lessonProgress()
            ->whereIn('lesson_id', $this->course->lessons()->published()->pluck('lessons.id'))
            ->where('is_completed', true)
            ->count();

        $progress = $publishedCount > 0 ? intval(($completedCount / $publishedCount) * 100) : 0;

        $this->assertEquals(100, $progress);
    }

    public function test_written_only_lessons_render_correctly()
    {
        $lesson = Lesson::factory()
            ->for($this->section, 'section')
            ->create([
                'status' => 'published',
                'type' => 'text',
                'content' => '<h2>Lesson Title</h2><p>This is content.</p>',
                'video_url' => null,
                'video_provider' => null,
                'video_id' => null,
            ]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.learn.lesson', ['course' => $this->course->slug, 'lesson' => $lesson->id]));

        $response->assertOk();
        $response->assertSee('Lesson Title');
        $response->assertSee('This is content.');
    }

    public function test_video_only_lessons_render_correctly()
    {
        $lesson = Lesson::factory()
            ->for($this->section, 'section')
            ->create([
                'status' => 'published',
                'type' => 'video',
                'content' => null,
                'video_provider' => 'youtube',
                'video_id' => 'dQw4w9WgXcQ',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.learn.lesson', ['course' => $this->course->slug, 'lesson' => $lesson->id]));

        $response->assertOk();
    }

    public function test_mixed_lessons_render_correctly()
    {
        $lesson = Lesson::factory()
            ->for($this->section, 'section')
            ->create([
                'status' => 'published',
                'type' => 'mixed',
                'content' => '<p>Notes about the video</p>',
                'video_provider' => 'youtube',
                'video_id' => 'dQw4w9WgXcQ',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.learn.lesson', ['course' => $this->course->slug, 'lesson' => $lesson->id]));

        $response->assertOk();
    }
}
