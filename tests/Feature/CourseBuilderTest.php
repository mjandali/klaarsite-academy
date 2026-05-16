<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Tests\TestCase;

class CourseBuilderTest extends TestCase
{
    private User $admin;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->course = Course::factory()
            ->for($this->admin)
            ->create(['status' => 'draft']);
    }

    public function test_admin_can_reorder_sections(): void
    {
        $firstSection = CourseSection::factory()
            ->for($this->course)
            ->create(['title' => 'First', 'order' => 1]);

        $secondSection = CourseSection::factory()
            ->for($this->course)
            ->create(['title' => 'Second', 'order' => 2]);

        $this->actingAs($this->admin)
            ->from(route('admin.courses.edit', $this->course))
            ->post(route('admin.sections.move', $secondSection), ['direction' => 'up'])
            ->assertRedirect(route('admin.courses.edit', $this->course));

        $this->assertSame(2, $firstSection->fresh()->order);
        $this->assertSame(1, $secondSection->fresh()->order);
    }

    public function test_admin_can_reorder_lessons_within_a_section(): void
    {
        $section = CourseSection::factory()
            ->for($this->course)
            ->create(['order' => 1]);

        $firstLesson = Lesson::factory()
            ->for($section, 'section')
            ->create([
                'title' => 'First Lesson',
                'status' => 'draft',
                'type' => 'text',
                'content' => '<p>First lesson</p>',
                'order' => 1,
            ]);

        $secondLesson = Lesson::factory()
            ->for($section, 'section')
            ->create([
                'title' => 'Second Lesson',
                'status' => 'draft',
                'type' => 'text',
                'content' => '<p>Second lesson</p>',
                'order' => 2,
            ]);

        $this->actingAs($this->admin)
            ->from(route('admin.courses.edit', $this->course))
            ->post(route('admin.lessons.move', $secondLesson), ['direction' => 'up'])
            ->assertRedirect(route('admin.courses.edit', $this->course));

        $this->assertSame(2, $firstLesson->fresh()->order);
        $this->assertSame(1, $secondLesson->fresh()->order);
    }
}
