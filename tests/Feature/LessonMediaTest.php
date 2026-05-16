<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonMediaTest extends TestCase
{
    public function test_admin_can_upload_a_lesson_image(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $lesson = $this->createLesson();

        $response = $this->actingAs($admin)->postJson(route('admin.lessons.media.store', $lesson), [
            'image' => UploadedFile::fake()->image('diagram.png', 1200, 800),
            'alt_text' => 'Architecture diagram',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('original_name', 'diagram.png');

        $media = LessonMedia::query()->firstOrFail();

        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame('Architecture diagram', $media->alt_text);
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertSame('/lesson-media/'.$media->id, $response->json('display_url'));
        Storage::disk('local')->assertExists($media->path);
    }

    public function test_student_enrolled_in_course_can_view_lesson_image(): void
    {
        Storage::fake('local');

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $lesson = $this->createLesson();
        $student->enrollments()->create([
            'course_id' => $lesson->section->course_id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        $path = 'lesson-media/'.$lesson->id.'/sample.webp';
        Storage::disk('local')->put($path, 'lesson-image');

        $media = $lesson->media()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'sample.webp',
            'mime_type' => 'image/webp',
            'size' => Storage::disk('local')->size($path),
            'width' => 960,
            'height' => 540,
        ]);

        $this->actingAs($student)
            ->get(route('lesson-media.show', $media))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');
    }

    public function test_guest_cannot_view_lesson_image(): void
    {
        Storage::fake('local');

        $lesson = $this->createLesson();
        $media = $this->createMediaForLesson($lesson);

        $this->get(route('lesson-media.show', $media))
            ->assertRedirect(route('login'));
    }

    public function test_non_enrolled_student_cannot_view_lesson_image(): void
    {
        Storage::fake('local');

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $lesson = $this->createLesson();
        $media = $this->createMediaForLesson($lesson);

        $this->actingAs($student)
            ->get(route('lesson-media.show', $media))
            ->assertForbidden();
    }

    public function test_svg_upload_is_rejected(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $lesson = $this->createLesson();

        $this->actingAs($admin)
            ->postJson(route('admin.lessons.media.store', $lesson), [
                'image' => UploadedFile::fake()->create('vector.svg', 20, 'image/svg+xml'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_lesson_deletion_removes_media_files(): void
    {
        Storage::fake('local');

        $lesson = $this->createLesson();
        $media = $this->createMediaForLesson($lesson, 'lesson-media/'.$lesson->id.'/cleanup.webp');

        $lesson->delete();

        Storage::disk('local')->assertMissing($media->path);
        $this->assertDatabaseMissing('lesson_media', [
            'id' => $media->id,
        ]);
    }

    private function createLesson(): Lesson
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $course = Course::factory()
            ->for($admin)
            ->published()
            ->create([
                'course_format' => 'mixed',
            ]);

        $section = CourseSection::factory()
            ->for($course)
            ->create(['order' => 1]);

        return Lesson::factory()
            ->for($section, 'section')
            ->published()
            ->create([
                'type' => 'text',
                'order' => 1,
                'content' => '<p>Written lesson body.</p>',
            ]);
    }

    private function createMediaForLesson(Lesson $lesson, ?string $path = null): LessonMedia
    {
        $path ??= 'lesson-media/'.$lesson->id.'/image.webp';
        Storage::disk('local')->put($path, 'lesson-image');

        return $lesson->media()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'image/webp',
            'size' => Storage::disk('local')->size($path),
            'width' => 1280,
            'height' => 720,
        ]);
    }
}
