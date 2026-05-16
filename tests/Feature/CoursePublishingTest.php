<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Carbon\Carbon;
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

        $this->actingAs($this->admin)
            ->put(route('admin.courses.update', $course), [
                'title' => $course->title,
                'subtitle' => $course->subtitle,
                'description' => $course->description,
                'price' => $course->price,
                'currency' => $course->currency,
                'level' => $course->level,
                'duration_hours' => $course->duration_hours,
                'meta_description' => $course->meta_description,
                'course_format' => $course->course_format,
                'status' => 'published',
                'slug' => $course->slug,
            ])
            ->assertRedirect();

        $this->assertNotNull($course->fresh()->published_at);
    }

    public function test_published_at_is_not_reset_when_editing_an_already_published_course(): void
    {
        $publishedAt = Carbon::parse('2026-05-01 10:00:00');
        $course = Course::factory()
            ->for($this->admin)
            ->create([
                'status' => 'published',
                'published_at' => $publishedAt,
            ]);

        Carbon::setTestNow($publishedAt->copy()->addDays(5));

        $this->actingAs($this->admin)
            ->put(route('admin.courses.update', $course), [
                'title' => $course->title.' Updated',
                'subtitle' => $course->subtitle,
                'description' => $course->description,
                'price' => $course->price,
                'currency' => $course->currency,
                'level' => $course->level,
                'duration_hours' => $course->duration_hours,
                'meta_description' => $course->meta_description,
                'course_format' => $course->course_format,
                'status' => 'published',
                'slug' => $course->slug,
            ])
            ->assertRedirect();

        $this->assertTrue($course->fresh()->published_at->equalTo($publishedAt));

        Carbon::setTestNow();
    }
}
