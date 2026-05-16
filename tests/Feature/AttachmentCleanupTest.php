<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_deleting_a_lesson_removes_its_attachment_files(): void
    {
        ['lesson' => $lesson, 'attachment' => $attachment] = $this->createAttachmentFixture();

        $this->actingAs($this->admin)
            ->delete(route('admin.lessons.destroy', $lesson))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($attachment->file_path);
        $this->assertDatabaseMissing('lesson_attachments', ['id' => $attachment->id]);
    }

    public function test_deleting_a_section_removes_nested_attachment_files(): void
    {
        ['section' => $section, 'attachment' => $attachment] = $this->createAttachmentFixture();

        $this->actingAs($this->admin)
            ->delete(route('admin.sections.destroy', $section))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($attachment->file_path);
        $this->assertDatabaseMissing('lesson_attachments', ['id' => $attachment->id]);
    }

    public function test_deleting_a_course_removes_all_nested_attachment_files(): void
    {
        ['course' => $course, 'attachment' => $attachment] = $this->createAttachmentFixture();

        $this->actingAs($this->admin)
            ->delete(route('admin.courses.destroy', $course))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($attachment->file_path);
        $this->assertDatabaseMissing('lesson_attachments', ['id' => $attachment->id]);
    }

    private function createAttachmentFixture(): array
    {
        $course = Course::factory()
            ->for($this->admin)
            ->create([
                'status' => 'published',
                'published_at' => now(),
            ]);

        $section = CourseSection::factory()
            ->for($course)
            ->create();

        $lesson = Lesson::factory()
            ->for($section, 'section')
            ->create([
                'status' => 'published',
            ]);

        $path = 'lesson-attachments/'.$lesson->id.'/notes.pdf';
        Storage::disk('local')->put($path, 'attachment body');

        $attachment = LessonAttachment::create([
            'lesson_id' => $lesson->id,
            'file_path' => $path,
            'file_name' => 'notes.pdf',
            'file_size' => Storage::disk('local')->size($path),
            'mime_type' => 'application/pdf',
        ]);

        return compact('course', 'section', 'lesson', 'attachment');
    }
}
