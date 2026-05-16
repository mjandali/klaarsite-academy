<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
