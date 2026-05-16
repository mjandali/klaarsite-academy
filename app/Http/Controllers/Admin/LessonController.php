<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Rules\AllowedLessonAttachment;
use App\Support\VideoEmbedParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException;

class LessonController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedLesson($request);
        $section = CourseSection::findOrFail($data['course_section_id']);
        $this->authorize('update', $section->course);
        $data['order'] = $data['order'] ?? ((int) $section->lessons()->max('order') + 1);

        $lesson = Lesson::create($data);
        $this->storeAttachments($request, $lesson);

        return back()->with('success', __('app.admin.lesson_saved'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson->load('section.course', 'attachments');
        $this->authorize('update', $lesson->section->course);

        $data = $this->validatedLesson($request);

        $lesson->update($data);
        $this->deleteRequestedAttachments($request, $lesson);
        $this->storeAttachments($request, $lesson);

        return back()->with('success', __('app.admin.lesson_saved'));
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف الدرس بنجاح.' : 'Lesson deleted successfully.');
    }

    public function move(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson->loadMissing('section.course');
        $this->authorize('update', $lesson->section->course);

        $data = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $swap = $lesson->section->lessons()
            ->where('id', '!=', $lesson->id)
            ->where('order', $data['direction'] === 'up' ? '<' : '>', $lesson->order)
            ->orderBy('order', $data['direction'] === 'up' ? 'desc' : 'asc')
            ->first();

        if (! $swap) {
            return back();
        }

        DB::transaction(function () use ($lesson, $swap): void {
            $originalOrder = $lesson->order;

            $lesson->update(['order' => $swap->order]);
            $swap->update(['order' => $originalOrder]);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث ترتيب الدروس.' : 'Lesson order updated.');
    }

    public function preview(Lesson $lesson): InertiaResponse
    {
        $lesson->loadMissing('attachments', 'media', 'section.course');
        $course = $lesson->section->course;
        $this->authorize('update', $course);

        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->orderBy('order'),
        ]);

        $orderedLessons = $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->values();

        $currentIndex = $orderedLessons->search(fn ($item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex !== false && $currentIndex > 0 ? $orderedLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex !== false ? $orderedLessons->get($currentIndex + 1) : null;

        return Inertia::render('Admin/Lessons/Preview', [
            'course' => $course,
            'currentLesson' => $lesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'builderUrl' => route('admin.courses.edit', $course),
        ]);
    }

    private function validatedLesson(Request $request): array
    {
        $data = $request->validate([
            'course_section_id' => ['required', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['video', 'text', 'mixed'])],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'file', 'max:20480', new AllowedLessonAttachment()],
            'delete_attachments' => ['nullable', 'array'],
            'delete_attachments.*' => ['integer', 'exists:lesson_attachments,id'],
        ]);

        $type = $data['type'] ?? 'text';
        $contentText = trim(strip_tags((string) ($data['content'] ?? '')));
        $hasImageContent = preg_match('/<img\b/i', (string) ($data['content'] ?? '')) === 1;

        try {
            $video = VideoEmbedParser::normalize($data['video_url'] ?? null);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'video_url' => $exception->getMessage(),
            ]);
        }

        if ($type === 'text' && $contentText === '' && ! $hasImageContent) {
            throw ValidationException::withMessages([
                'content' => app()->getLocale() === 'ar'
                    ? 'محتوى الدرس النصي مطلوب.'
                    : 'Written lesson content is required.',
            ]);
        }

        if ($type === 'video' && empty($video['video_id'])) {
            throw ValidationException::withMessages([
                'video_url' => app()->getLocale() === 'ar'
                    ? 'رابط الفيديو مطلوب للدروس المرئية.'
                    : 'A video URL is required for video lessons.',
            ]);
        }

        if ($type === 'mixed' && $contentText === '' && ! $hasImageContent && empty($video['video_id'])) {
            throw ValidationException::withMessages([
                'content' => app()->getLocale() === 'ar'
                    ? 'الدرس المختلط يحتاج محتوى مكتوباً أو فيديو واحداً على الأقل.'
                    : 'Mixed lessons need written content or a supported video.',
            ]);
        }

        if ($type === 'text') {
            $video = [
                'video_url' => null,
                'video_provider' => null,
                'video_id' => null,
            ];
        }

        unset($data['attachments'], $data['delete_attachments']);

        return array_merge($data, $video);
    }

    private function storeAttachments(Request $request, Lesson $lesson): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('lesson-attachments/'.$lesson->id, 'local');

            $lesson->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            ]);
        }
    }

    private function deleteRequestedAttachments(Request $request, Lesson $lesson): void
    {
        $attachments = $lesson->attachments()
            ->whereIn('id', $request->input('delete_attachments', []))
            ->get();

        foreach ($attachments as $attachment) {
            $attachment->delete();
        }
    }
}
