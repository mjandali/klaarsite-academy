<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Tests\TestCase;

class CoursePublishingTest extends TestCase
{
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    public function test_draft_courses_are_hidden_from_public_pages()
    {
        $draftCourse = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'draft']);

        $response = $this->get('/courses');
        $response->assertDontSee($draftCourse->title);
    }

    public function test_published_courses_appear_on_public_pages()
    {
        $publishedCourse = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'published', 'published_at' => now()]);

        $response = $this->get('/courses');
        $response->assertSee($publishedCourse->title);
    }

    public function test_admin_can_preview_draft_courses()
    {
        $draftCourse = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/courses/{$draftCourse->id}/edit");

        $response->assertOk();
    }

    public function test_student_cannot_access_draft_course_detail()
    {
        $draftCourse = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'draft']);

        $response = $this->get("/courses/{$draftCourse->slug}");
        $response->assertNotFound();
    }

    public function test_archived_courses_are_hidden()
    {
        $archivedCourse = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'archived']);

        $response = $this->get('/courses');
        $response->assertDontSee($archivedCourse->title);
    }

    public function test_published_at_is_set_when_publishing()
    {
        $course = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'draft', 'published_at' => null]);

        $this->assertNull($course->published_at);

        $course->update(['status' => 'published', 'published_at' => now()]);

        $this->assertNotNull($course->fresh()->published_at);
    }
}
