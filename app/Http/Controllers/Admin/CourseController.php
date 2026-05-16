<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\WebpImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount(['sections', 'lessons', 'enrollments'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Courses/Index', ['courses' => $courses]);
    }

    public function create()
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $data = $this->validatedCourse($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = $request->input('status', 'draft');
        $data['published_at'] = $request->input('status') === 'published' ? now() : null;
        $data['course_format'] = $data['course_format'] ?? 'mixed';

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_url'] = $this->storeThumbnailAsWebp($request);
        }

        $course = Course::create($data);

        return redirect()->route('admin.courses.edit', $course)->with('success', __('app.admin.course_saved'));
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $course->load([
            'sections.lessons.attachments',
            'sections.lessons.media',
        ])->loadCount(['sections', 'lessons', 'enrollments']);

        return Inertia::render('Admin/Courses/Edit', ['course' => $course]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $this->validatedCourse($request, $course);
        $status = $request->input('status', 'draft');
        $data['status'] = $status;
        $data['published_at'] = $status === 'published'
            ? ($course->published_at ?? now())
            : $course->published_at;
        $data['course_format'] = $data['course_format'] ?? 'mixed';

        if ($request->filled('slug')) {
            $data['slug'] = $this->uniqueSlug($request->string('slug')->toString(), $course->id);
        } elseif ($course->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $course->id);
        }

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $data['thumbnail_url'] = $this->storeThumbnailAsWebp($request);
        }

        $course->update($data);

        return back()->with('success', __('app.admin.course_saved'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', __('app.admin.course_deleted'));
    }

    public function structure(Course $course)
    {
        $this->authorize('update', $course);

        return redirect()->route('admin.courses.edit', $course);
    }

    public function reorderSections(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:course_sections,id',
            'sections.*.lessons' => 'required|array',
            'sections.*.lessons.*.id' => 'required|exists:lessons,id',
        ]);

        foreach ($request->sections as $sectionIndex => $sectionData) {
            $course->sections()
                ->where('id', $sectionData['id'])
                ->update(['order' => $sectionIndex + 1]);

            foreach ($sectionData['lessons'] as $lessonIndex => $lessonData) {
                \App\Models\Lesson::where('id', $lessonData['id'])
                    ->update(['order' => $lessonIndex + 1]);
            }
        }

        return response()->json(['success' => true]);
    }

    private function validatedCourse(Request $request, ?Course $course = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'currency' => ['required', 'string', 'size:3'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'course_format' => ['required', Rule::in(['text', 'video', 'mixed'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'slug' => ['nullable', 'string', 'max:190'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function storeThumbnailAsWebp(Request $request): string
    {
        try {
            return app(WebpImageStore::class)->store($request->file('thumbnail'));
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'thumbnail' => $exception->getMessage(),
            ]);
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'course-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 2;
        while (Course::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
