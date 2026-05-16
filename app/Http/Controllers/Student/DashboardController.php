<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()
            ->with(['course' => fn ($query) => $query->withCount(['sections', 'lessons'])])
            ->latest()
            ->paginate(10)
            ->through(fn ($enrollment) => $this->withContinueUrl($enrollment, $user->id));

        return Inertia::render('Student/Dashboard', [
            'enrollments' => $enrollments,
            'stats' => [
                'total' => $user->enrollments()->count(),
                'in_progress' => $user->enrollments()->where('progress_percentage', '<', 100)->count(),
                'completed' => $user->enrollments()->where('progress_percentage', '>=', 100)->count(),
            ],
        ]);
    }

    public function myCourses()
    {
        $user = auth()->user();
        $courses = $user->enrollments()
            ->with(['course' => fn ($query) => $query->withCount(['sections', 'lessons'])])
            ->latest()
            ->paginate(12)
            ->through(fn ($enrollment) => $this->withContinueUrl($enrollment, $user->id));

        return Inertia::render('Student/MyCourses', ['courses' => $courses]);
    }

    public function orders()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('course')->latest()->paginate(20);

        return Inertia::render('Student/Orders', ['orders' => $orders]);
    }

    private function withContinueUrl($enrollment, int $userId)
    {
        $lastLesson = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('lesson_progress.user_id', $userId)
            ->where('course_sections.course_id', $enrollment->course_id)
            ->latest('lesson_progress.last_accessed_at')
            ->value('lesson_progress.lesson_id');

        $enrollment->continue_url = $lastLesson
            ? route('student.learn.lesson', [$enrollment->course, $lastLesson])
            : route('student.learn.course', $enrollment->course);

        return $enrollment;
    }
}
