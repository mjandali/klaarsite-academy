<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseSectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        $this->authorize('update', $course);
        $data['order'] = $data['order'] ?? ((int) $course->sections()->max('order') + 1);

        CourseSection::create($data);

        return back()->with('success', __('app.admin.section_saved'));
    }

    public function update(Request $request, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section->course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $section->update($data);

        return back()->with('success', __('app.admin.section_saved'));
    }

    public function destroy(CourseSection $section): RedirectResponse
    {
        $this->authorize('delete', $section->course);
        $section->delete();

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف القسم بنجاح.' : 'Section deleted successfully.');
    }

    public function move(Request $request, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section->course);

        $data = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $swap = $section->course->sections()
            ->where('id', '!=', $section->id)
            ->where('order', $data['direction'] === 'up' ? '<' : '>', $section->order)
            ->orderBy('order', $data['direction'] === 'up' ? 'desc' : 'asc')
            ->first();

        if (! $swap) {
            return back();
        }

        DB::transaction(function () use ($section, $swap): void {
            $originalOrder = $section->order;

            $section->update(['order' => $swap->order]);
            $swap->update(['order' => $originalOrder]);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث ترتيب الأقسام.' : 'Section order updated.');
    }
}
