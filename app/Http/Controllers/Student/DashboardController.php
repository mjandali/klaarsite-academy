<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()
            ->with(['course' => fn ($query) => $query->withCount([
                'sections',
                'lessons' => fn ($lessonQuery) => $lessonQuery->published(),
            ])])
            ->latest()
            ->paginate(10)
            ->through(fn ($enrollment) => $this->withContinueContext($enrollment, $user->id));

        return Inertia::render('Student/Dashboard', [
            'enrollments' => $enrollments,
            'stats' => [
                'total' => $user->enrollments()->count(),
                'in_progress' => $user->enrollments()->where('progress_percentage', '<', 100)->count(),
                'completed' => $user->enrollments()->where('progress_percentage', '>=', 100)->count(),
            ],
        ]);
    }

    public function myCourses(): InertiaResponse
    {
        $user = auth()->user();
        $courses = $user->enrollments()
            ->with(['course' => fn ($query) => $query->withCount([
                'sections',
                'lessons' => fn ($lessonQuery) => $lessonQuery->published(),
            ])])
            ->latest()
            ->paginate(12)
            ->through(fn ($enrollment) => $this->withContinueContext($enrollment, $user->id));

        return Inertia::render('Student/MyCourses', ['courses' => $courses]);
    }

    public function orders(): InertiaResponse
    {
        $user = auth()->user();
        $orders = $user->orders()->with('course')->latest()->paginate(20);

        return Inertia::render('Student/Orders', ['orders' => $orders]);
    }

    private function withContinueContext($enrollment, int $userId)
    {
        $course = $enrollment->course;
        $lessonIds = Lesson::query()
            ->select('lessons.id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('course_sections.course_id', $course->id)
            ->where('lessons.status', 'published')
            ->orderBy('course_sections.order')
            ->orderBy('lessons.order')
            ->pluck('lessons.id');

        $lastLessonId = $lessonIds->isEmpty()
            ? null
            : LessonProgress::query()
                ->where('lesson_progress.user_id', $userId)
                ->whereIn('lesson_progress.lesson_id', $lessonIds)
                ->latest('lesson_progress.last_accessed_at')
                ->value('lesson_progress.lesson_id');

        $firstLessonId = $lessonIds->first();
        $isCompleted = (int) $enrollment->progress_percentage >= 100;

        if ($isCompleted || ! $firstLessonId) {
            $enrollment->continue_url = route('student.learn.course', $course);
            $enrollment->continue_mode = $isCompleted ? 'review' : 'overview';

            return $enrollment;
        }

        $enrollment->continue_url = route('student.learn.lesson', [$course, $lastLessonId ?: $firstLessonId]);
        $enrollment->continue_mode = $lastLessonId ? 'continue' : 'start';

        return $enrollment;
    }
}
