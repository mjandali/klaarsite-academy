<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::published()
            ->with('user:id,name')
            ->withCount([
                'sections',
                'lessons' => fn ($query) => $query->published(),
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course)
    {
        if (! $course->isPublished()) {
            abort(404);
        }

        $this->authorize('view', $course);

        $course->load([
            'user:id,name',
            'sections.lessons' => fn ($query) => $query->published()->orderBy('order'),
        ])->loadCount([
            'sections',
            'lessons' => fn ($query) => $query->published(),
        ]);

        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = auth()->user()->enrollments()
                ->where('course_id', $course->id)
                ->exists();
        }

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
        ]);
    }
}
