<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LearningController extends Controller
{
    public function course(Request $request, Course $course): InertiaResponse
    {
        $this->authorize('viewContent', $course);
        $enrollment = $this->enrollmentOrFail($request, $course)->fresh();
        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->published()->withCount(['assessmentQuestions as exercise_questions_count' => fn ($assessmentQuery) => $assessmentQuery->active()->where('assessment_type', AssessmentQuestion::ASSESSMENT_LESSON)])->orderBy('order'),
        ]);

        $completedLessonIds = $request->user()->lessonProgress()
            ->where('is_completed', true)
            ->whereIn('lesson_id', $course->lessons()->published()->pluck('lessons.id'))
            ->pluck('lesson_id')
            ->values();

        $orderedLessons = $this->orderedPublishedLessons($course);
        $resumeLesson = $this->lastOrFirstLesson($request, $course);

        return Inertia::render('Student/Learn/Course', [
            'course' => $course,
            'enrollment' => $enrollment,
            'completedLessonIds' => $completedLessonIds,
            'resumeUrl' => $resumeLesson ? route('student.learn.lesson', [$course, $resumeLesson]) : null,
            'courseCompleted' => (int) $enrollment->progress_percentage >= 100,
            'publishedLessonsCount' => $orderedLessons->count(),
            'finalExamQuestionsCount' => AssessmentQuestion::query()->forFinalExam($course)->active()->count(),
        ]);
    }

    public function lesson(Request $request, Course $course, Lesson $lesson): InertiaResponse
    {
        $this->authorize('viewContent', $course);
        $enrollment = $this->enrollmentOrFail($request, $course);
        $this->abortIfLessonNotInCourse($lesson, $course);
        $this->authorize('view', $lesson);

        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->published()->withCount(['assessmentQuestions as exercise_questions_count' => fn ($assessmentQuery) => $assessmentQuery->active()->where('assessment_type', AssessmentQuestion::ASSESSMENT_LESSON)])->orderBy('order'),
        ]);
        $lesson->load('attachments', 'media', 'section');
        $lesson->loadCount(['assessmentQuestions as exercise_questions_count' => fn ($query) => $query->active()->where('assessment_type', AssessmentQuestion::ASSESSMENT_LESSON)]);

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['last_accessed_at' => now()]
        );

        $completedLessonIds = $request->user()->lessonProgress()
            ->where('is_completed', true)
            ->whereIn('lesson_id', $course->lessons()->published()->pluck('lessons.id'))
            ->pluck('lesson_id')
            ->values();

        $orderedLessons = $this->orderedPublishedLessons($course);
        $currentIndex = $orderedLessons->search(fn ($item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex !== false && $currentIndex > 0 ? $orderedLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex !== false ? $orderedLessons->get($currentIndex + 1) : null;

        return Inertia::render('Student/Learn/Lesson', [
            'course' => $course,
            'enrollment' => $enrollment->fresh(),
            'currentLesson' => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'isCompleted' => $completedLessonIds->contains($lesson->id),
            'courseOverviewUrl' => route('student.learn.course', $course),
            'courseCompleted' => (int) $enrollment->progress_percentage >= 100,
            'exerciseQuestionsCount' => (int) $lesson->exercise_questions_count,
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson->load('section.course');
        $course = $lesson->section->course;
        $this->authorize('view', $lesson);
        $this->enrollmentOrFail($request, $course);

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['is_completed' => true, 'completed_at' => now(), 'last_accessed_at' => now()]
        );

        $this->refreshEnrollmentProgress($request->user()->id, $course);

        return back()->with('success', __('app.student.lesson_completed'));
    }

    public function download(Request $request, LessonAttachment $attachment): BinaryFileResponse
    {
        $attachment->load('lesson.section.course');
        $this->authorize('download', $attachment);
        $this->enrollmentOrFail($request, $attachment->lesson->section->course);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->file_path), 404);

        return response()->download($disk->path($attachment->file_path), $attachment->file_name);
    }

    private function enrollmentOrFail(Request $request, Course $course): Enrollment
    {
        $enrollment = $request->user()->enrollments()->where('course_id', $course->id)->first();
        abort_unless($enrollment, 403);

        return $enrollment;
    }

    private function abortIfLessonNotInCourse(Lesson $lesson, Course $course): void
    {
        abort_unless(
            $lesson->section()->where('course_id', $course->id)->exists() && $lesson->isPublished(),
            404
        );
    }

    private function lastOrFirstLesson(Request $request, Course $course): ?Lesson
    {
        $lessonIds = $course->sections->flatMap(fn ($section) => $section->lessons->pluck('id'));
        $lastProgress = $request->user()->lessonProgress()
            ->whereIn('lesson_id', $lessonIds)
            ->latest('last_accessed_at')
            ->first();

        if ($lastProgress) {
            return $course->sections
                ->flatMap(fn ($section) => $section->lessons)
                ->firstWhere('id', $lastProgress->lesson_id);
        }

        return $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->first();
    }

    private function orderedPublishedLessons(Course $course)
    {
        return $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->values();
    }

    private function refreshEnrollmentProgress(int $userId, Course $course): void
    {
        $lessonIds = $course->lessons()->published()->pluck('lessons.id');
        $total = $lessonIds->count();
        $completed = LessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        Enrollment::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->update([
                'progress_percentage' => $progress,
                'completed_at' => $progress >= 100 ? now() : null,
            ]);
    }
}
