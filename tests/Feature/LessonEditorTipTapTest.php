<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonEditorTipTapTest extends TestCase
{
    private User $admin;
    private User $student;
    private Course $course;
    private CourseSection $section;
    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create(['role' => 'student']);

        $this->course = Course::factory()
            ->for($this->admin)
            ->published()
            ->create([
                'course_format' => 'mixed',
            ]);

        $this->section = CourseSection::factory()
            ->for($this->course)
            ->create(['order' => 1]);

        $this->lesson = Lesson::factory()
            ->for($this->section, 'section')
            ->published()
            ->create([
                'type' => 'mixed',
                'order' => 1,
                'title' => 'Original lesson',
                'content' => '<p>Old lesson content.</p>',
            ]);
    }

    public function test_safe_tiptap_html_is_accepted_when_saving_a_lesson(): void
    {
        $media = $this->createLessonMedia();

        $safeHtml = '<h2>Getting Started</h2>'
            .'<p><strong>Bold intro</strong> with <em>helpful notes</em>.</p>'
            .'<ul><li>First step</li><li>Second step</li></ul>'
            .'<blockquote>Remember the basics.</blockquote>'
            .'<figure class="media-center media-w-50"><img src="/lesson-media/'.$media->id.'" alt="Diagram"><figcaption>Helpful diagram</figcaption></figure>'
            .'<pre><code>const answer = 42;</code></pre>';

        $this->updateLesson([
            'content' => $safeHtml,
        ])->assertRedirect();

        $this->lesson->refresh();

        $this->assertStringContainsString('<h2>Getting Started</h2>', $this->lesson->content);
        $this->assertStringContainsString('<strong>Bold intro</strong>', $this->lesson->content);
        $this->assertStringContainsString('<em>helpful notes</em>', $this->lesson->content);
        $this->assertStringContainsString('<ul><li>First step</li><li>Second step</li></ul>', $this->lesson->content);
        $this->assertStringContainsString('<blockquote>Remember the basics.</blockquote>', $this->lesson->content);
        $this->assertStringContainsString('<figure class="media-center media-w-50">', $this->lesson->content);
        $this->assertStringContainsString('<figcaption>Helpful diagram</figcaption>', $this->lesson->content);
        $this->assertStringContainsString('<pre><code>const answer = 42;</code></pre>', $this->lesson->content);
    }

    public function test_unsafe_html_is_removed_when_saving_a_lesson(): void
    {
        $media = $this->createLessonMedia();

        $unsafeHtml = '<p onclick="evil()" style="color:red">Intro</p>'
            .'<script>alert(1)</script>'
            .'<figure class="media-center media-w-50 arbitrary-class" style="padding:1rem">'
            .'<img src="/lesson-media/'.$media->id.'" alt="Diagram" class="media-w-50 bad-class" onerror="evil()" srcset="/lesson-media/999 2x">'
            .'<figcaption onclick="bad()">Caption</figcaption>'
            .'</figure>'
            .'<img src="https://example.com/remote.png" alt="External">';

        $this->updateLesson([
            'content' => $unsafeHtml,
        ])->assertRedirect();

        $this->lesson->refresh();

        $this->assertStringContainsString('<p>Intro</p>', $this->lesson->content);
        $this->assertStringContainsString('<figure class="media-center media-w-50">', $this->lesson->content);
        $this->assertStringContainsString('<img src="/lesson-media/'.$media->id.'"', $this->lesson->content);
        $this->assertStringContainsString('alt="Diagram"', $this->lesson->content);
        $this->assertStringContainsString('class="media-w-50"', $this->lesson->content);
        $this->assertStringContainsString('<figcaption>Caption</figcaption>', $this->lesson->content);
        $this->assertStringNotContainsString('<script', $this->lesson->content);
        $this->assertStringNotContainsString('onclick=', $this->lesson->content);
        $this->assertStringNotContainsString('style=', $this->lesson->content);
        $this->assertStringNotContainsString('arbitrary-class', $this->lesson->content);
        $this->assertStringNotContainsString('bad-class', $this->lesson->content);
        $this->assertStringNotContainsString('srcset=', $this->lesson->content);
        $this->assertStringNotContainsString('example.com/remote.png', $this->lesson->content);
    }

    public function test_existing_lesson_content_can_be_edited_and_saved(): void
    {
        $media = $this->createLessonMedia();

        $this->assertStringContainsString('Old lesson content.', $this->lesson->content);

        $updatedHtml = '<h3>Updated section</h3>'
            .'<p>Fresh lesson explanation.</p>'
            .'<figure class="media-end media-w-33 media-wrap"><img src="/lesson-media/'.$media->id.'" alt="Updated image"><figcaption>Updated caption</figcaption></figure>';

        $this->updateLesson([
            'title' => 'Updated lesson',
            'content' => $updatedHtml,
        ])->assertRedirect();

        $this->lesson->refresh();

        $this->assertSame('Updated lesson', $this->lesson->title);
        $this->assertStringContainsString('<h3>Updated section</h3>', $this->lesson->content);
        $this->assertStringContainsString('Fresh lesson explanation.', $this->lesson->content);
        $this->assertStringContainsString('Updated caption', $this->lesson->content);
        $this->assertStringNotContainsString('Old lesson content.', $this->lesson->content);
    }

    public function test_lesson_content_with_images_renders_on_student_page(): void
    {
        $media = $this->createLessonMedia();

        $this->lesson->update([
            'content' => '<p>Student-facing explanation.</p><figure class="media-start media-w-33 media-wrap"><img src="/lesson-media/'.$media->id.'" alt="Diagram"><figcaption>Rendered caption</figcaption></figure><pre><code>console.log("hello");</code></pre>',
        ]);

        $this->student->enrollments()->create([
            'course_id' => $this->course->id,
            'progress_percentage' => 0,
        ]);

        $this->actingAs($this->student)
            ->get(route('student.learn.lesson', [$this->course, $this->lesson]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Learn/Lesson')
                ->where('currentLesson.content', fn (string $content) => str_contains($content, '/lesson-media/'.$media->id)
                    && str_contains($content, 'Rendered caption')
                    && str_contains($content, 'console.log'))
            );
    }

    public function test_lesson_editor_uses_the_rich_text_component_instead_of_the_old_content_textarea(): void
    {
        $lessonEditor = file_get_contents(resource_path('js/Components/LessonEditor.vue'));
        $richEditor = file_get_contents(resource_path('js/Components/LessonRichTextEditor.vue'));

        $this->assertIsString($lessonEditor);
        $this->assertIsString($richEditor);
        $this->assertStringContainsString('LessonRichTextEditor', $lessonEditor);
        $this->assertSame(0, preg_match('/<textarea[^>]+v-model="form\.content"/', $lessonEditor));
        $this->assertStringNotContainsString('ref="contentEditor"', $lessonEditor);
        $this->assertStringNotContainsString('insertHeading(', $lessonEditor);
        $this->assertStringNotContainsString('insertParagraph(', $lessonEditor);
        $this->assertStringNotContainsString('insertMediaSnippet(', $lessonEditor);
        $this->assertStringContainsString('useEditor({', $richEditor);
    }

    public function test_editor_actions_do_not_use_browser_alert_confirm_or_prompt(): void
    {
        $lessonEditor = file_get_contents(resource_path('js/Components/LessonEditor.vue'));
        $richEditor = file_get_contents(resource_path('js/Components/LessonRichTextEditor.vue'));
        $combined = $lessonEditor.$richEditor;

        $this->assertIsString($lessonEditor);
        $this->assertIsString($richEditor);
        $this->assertStringNotContainsString('window.alert', $combined);
        $this->assertStringNotContainsString('window.confirm', $combined);
        $this->assertStringNotContainsString('window.prompt', $combined);
        $this->assertStringNotContainsString('alert(', $richEditor);
        $this->assertStringNotContainsString('confirm(', $richEditor);
        $this->assertStringNotContainsString('prompt(', $richEditor);
    }


    public function test_lesson_editor_opens_in_a_large_modal_and_has_direction_controls(): void
    {
        $courseEdit = file_get_contents(resource_path('js/Pages/Admin/Courses/Edit.vue'));
        $richEditor = file_get_contents(resource_path('js/Components/LessonRichTextEditor.vue'));
        $textBlockExtension = file_get_contents(resource_path('js/Components/editor/TextBlockAttributes.js'));

        $this->assertIsString($courseEdit);
        $this->assertIsString($richEditor);
        $this->assertIsString($textBlockExtension);
        $this->assertStringContainsString('<Teleport to="body">', $courseEdit);
        $this->assertStringContainsString('h-[92vh]', $courseEdit);
        $this->assertStringContainsString('lesson-editor-modal__body', $courseEdit);
        $this->assertStringContainsString('sticky top-0', $richEditor);
        $this->assertStringContainsString("setTextDirection('rtl')", $richEditor);
        $this->assertStringContainsString("setTextDirection('ltr')", $richEditor);
        $this->assertStringContainsString("setTextAlignment('left')", $richEditor);
        $this->assertStringContainsString('TextBlockAttributes', $richEditor);
    }

    private function updateLesson(array $overrides = [])
    {
        return $this->actingAs($this->admin)
            ->put(route('admin.lessons.update', $this->lesson), array_merge([
                'course_section_id' => $this->section->id,
                'title' => $this->lesson->title,
                'description' => $this->lesson->description,
                'type' => 'mixed',
                'content' => $this->lesson->content,
                'video_url' => '',
                'duration_minutes' => 15,
                'status' => 'published',
                'order' => 1,
            ], $overrides));
    }

    private function createLessonMedia(): LessonMedia
    {
        return $this->lesson->media()->create([
            'disk' => 'local',
            'path' => 'lesson-media/'.$this->lesson->id.'/diagram.webp',
            'original_name' => 'diagram.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
            'width' => 1200,
            'height' => 800,
            'alt_text' => 'Diagram',
        ]);
    }
}
