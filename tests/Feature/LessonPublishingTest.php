<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonPublishingTest extends TestCase
{
    private User $admin;
    private User $student;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create(['role' => 'student']);

        $this->course = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'published', 'published_at' => now()]);
    }

    public function test_draft_lessons_are_hidden_from_students()
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create();

        $draftLesson = Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'draft']);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.learn.lesson', ['course' => $this->course->slug, 'lesson' => $draftLesson->id]));

        $response->assertNotFound();
    }

    public function test_published_lessons_are_visible_to_enrolled_students()
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create();

        $publishedLesson = Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'published']);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.learn.lesson', ['course' => $this->course->slug, 'lesson' => $publishedLesson->id]));

        $response->assertOk();
    }

    public function test_admin_can_preview_draft_lessons()
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create();

        $draftLesson = Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.lessons.preview', ['lesson' => $draftLesson->id]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Lessons/Preview')
            ->where('currentLesson.id', $draftLesson->id)
            ->where('currentLesson.status', 'draft')
        );
    }

    public function test_public_course_detail_counts_only_published_lessons(): void
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create();

        Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'draft']);

        Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'published']);

        $this->get(route('courses.show', $this->course))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('course.lessons_count', 1)
            );
    }

    public function test_draft_lessons_do_not_count_in_progress()
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create();

        $draftLesson = Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'draft']);

        $publishedLesson = Lesson::factory()
            ->for($section, 'section')
            ->create(['status' => 'published']);

        $this->student->enrollments()->create(['course_id' => $this->course->id]);

        // Mark both as complete
        $this->student->lessonProgress()->create([
            'lesson_id' => $draftLesson->id,
            'is_completed' => true,
        ]);

        $this->student->lessonProgress()->create([
            'lesson_id' => $publishedLesson->id,
            'is_completed' => true,
        ]);

        $publishedCount = $this->course->lessons()->published()->count();
        $completedCount = $this->student->lessonProgress()
            ->whereIn('lesson_id', $this->course->lessons()->published()->pluck('lessons.id'))
            ->where('is_completed', true)
            ->count();

        $this->assertEquals(1, $publishedCount);
        $this->assertEquals(1, $completedCount);
    }
}
