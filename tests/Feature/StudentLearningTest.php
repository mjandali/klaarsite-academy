<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_continue_learning_opens_last_accessed_lesson(): void
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $lesson2 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $this->student->lessonProgress()->create([
            'lesson_id' => $lesson1->id,
            'last_accessed_at' => now()->subMinutes(5),
        ]);

        $this->student->lessonProgress()->create([
            'lesson_id' => $lesson2->id,
            'last_accessed_at' => now(),
        ]);

        $this->actingAs($this->student)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('enrollments.data.0.continue_url', route('student.learn.lesson', [$this->course, $lesson2]))
                ->where('enrollments.data.0.continue_mode', 'continue')
            );
    }

    public function test_continue_learning_opens_first_lesson_if_nothing_accessed(): void
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $this->actingAs($this->student)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('enrollments.data.0.continue_url', route('student.learn.lesson', [$this->course, $lesson1]))
                ->where('enrollments.data.0.continue_mode', 'start')
            );
    }

    public function test_completed_course_continue_learning_returns_course_overview(): void
    {
        Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $this->student->enrollments()->create([
            'course_id' => $this->course->id,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->student)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('enrollments.data.0.continue_url', route('student.learn.course', $this->course))
                ->where('enrollments.data.0.continue_mode', 'review')
            );
    }

    public function test_student_dashboard_counts_only_published_lessons(): void
    {
        Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'draft', 'order' => 1]);

        Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $this->actingAs($this->student)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('enrollments.data.0.course.lessons_count', 1)
            );
    }

    public function test_previous_and_next_lesson_navigation_are_available(): void
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $lesson2 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $lesson3 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 3]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $this->actingAs($this->student)
            ->get(route('student.learn.lesson', [$this->course, $lesson2]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Learn/Lesson')
                ->where('previousLesson.id', $lesson1->id)
                ->where('nextLesson.id', $lesson3->id)
                ->where('courseOverviewUrl', route('student.learn.course', $this->course))
            );
    }

    public function test_course_completion_is_100_percent_when_all_published_lessons_completed(): void
    {
        $lesson1 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $lesson2 = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 2]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

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

    public function test_written_only_lessons_render_correctly(): void
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

    public function test_video_only_lessons_render_correctly(): void
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

    public function test_mixed_lessons_render_correctly(): void
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

    public function test_legacy_student_lesson_routes_are_not_accessible(): void
    {
        $lesson = Lesson::factory()
            ->for($this->section, 'section')
            ->create(['status' => 'published', 'order' => 1]);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $this->actingAs($this->student)
            ->get("/dashboard/lesson/{$lesson->id}")
            ->assertNotFound();

        $this->actingAs($this->student)
            ->post("/dashboard/lessons/{$lesson->id}/track-watch", [
                'watched_seconds' => 30,
            ])
            ->assertNotFound();
    }
}
