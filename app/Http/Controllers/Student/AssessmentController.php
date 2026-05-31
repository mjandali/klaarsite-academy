<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\AssessmentGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AssessmentController extends Controller
{
    public function lesson(Request $request, Course $course, Lesson $lesson): InertiaResponse
    {
        $this->authorize('viewContent', $course);
        $enrollment = $this->enrollmentOrFail($request, $course);
        $this->abortIfLessonNotInCourse($lesson, $course);

        $questions = $this->lessonQuestions($course, $lesson)->get();
        abort_if($questions->isEmpty(), 404);
        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->published()->orderBy('order'),
        ]);


        $attempt = $this->requestedAttempt($request, $course, $lesson, AssessmentQuestion::ASSESSMENT_LESSON);
        $completedLessonIds = $this->completedLessonIds($request, $course);

        return Inertia::render('Student/Learn/Assessment', [
            'course' => $course,
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'completedLessonIds' => $completedLessonIds,
            'courseCompleted' => (int) $enrollment->progress_percentage >= 100,
            'assessmentType' => AssessmentQuestion::ASSESSMENT_LESSON,
            'title' => app()->getLocale() === 'ar' ? 'تمرين الدرس' : 'Lesson Exercise',
            'questions' => $this->publicQuestions($questions),
            'attempt' => $this->attemptPayload($attempt, $request->boolean('show_answers')),
            'submitUrl' => route('student.learn.lesson.exercise.submit', [$course, $lesson]),
            'backUrl' => route('student.learn.lesson', [$course, $lesson]),
            'showAnswersUrl' => $attempt ? route('student.learn.lesson.exercise', [$course, $lesson, 'attempt' => $attempt->id, 'show_answers' => 1]) : null,
        ]);
    }

    public function submitLesson(Request $request, Course $course, Lesson $lesson, AssessmentGrader $grader): RedirectResponse
    {
        $this->authorize('viewContent', $course);
        $this->enrollmentOrFail($request, $course);
        $this->abortIfLessonNotInCourse($lesson, $course);

        $questions = $this->lessonQuestions($course, $lesson)->get();
        abort_if($questions->isEmpty(), 404);

        $attempt = $this->storeAttempt($request, $course, $lesson, AssessmentQuestion::ASSESSMENT_LESSON, $questions, $grader);

        return redirect()
            ->route('student.learn.lesson.exercise', [$course, $lesson, 'attempt' => $attempt->id])
            ->with('success', app()->getLocale() === 'ar' ? 'تم إرسال الإجابات وحساب النتيجة.' : 'Answers submitted and graded.');
    }

    public function finalExam(Request $request, Course $course): InertiaResponse
    {
        $this->authorize('viewContent', $course);
        $enrollment = $this->enrollmentOrFail($request, $course);

        $questions = $this->finalExamQuestions($course)->get();
        abort_if($questions->isEmpty(), 404);
        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->published()->orderBy('order'),
        ]);


        $attempt = $this->requestedAttempt($request, $course, null, AssessmentQuestion::ASSESSMENT_FINAL_EXAM);
        $completedLessonIds = $this->completedLessonIds($request, $course);

        return Inertia::render('Student/Learn/Assessment', [
            'course' => $course,
            'lesson' => null,
            'enrollment' => $enrollment,
            'completedLessonIds' => $completedLessonIds,
            'courseCompleted' => (int) $enrollment->progress_percentage >= 100,
            'assessmentType' => AssessmentQuestion::ASSESSMENT_FINAL_EXAM,
            'title' => app()->getLocale() === 'ar' ? 'الاختبار النهائي' : 'Final Exam',
            'questions' => $this->publicQuestions($questions),
            'attempt' => $this->attemptPayload($attempt, $request->boolean('show_answers')),
            'submitUrl' => route('student.learn.final-exam.submit', $course),
            'backUrl' => route('student.learn.course', $course),
            'showAnswersUrl' => $attempt ? route('student.learn.final-exam', [$course, 'attempt' => $attempt->id, 'show_answers' => 1]) : null,
        ]);
    }

    public function submitFinalExam(Request $request, Course $course, AssessmentGrader $grader): RedirectResponse
    {
        $this->authorize('viewContent', $course);
        $this->enrollmentOrFail($request, $course);

        $questions = $this->finalExamQuestions($course)->get();
        abort_if($questions->isEmpty(), 404);

        $attempt = $this->storeAttempt($request, $course, null, AssessmentQuestion::ASSESSMENT_FINAL_EXAM, $questions, $grader);

        return redirect()
            ->route('student.learn.final-exam', [$course, 'attempt' => $attempt->id])
            ->with('success', app()->getLocale() === 'ar' ? 'تم إرسال الاختبار وحساب النتيجة النهائية.' : 'Final exam submitted and graded.');
    }

    private function storeAttempt(Request $request, Course $course, ?Lesson $lesson, string $assessmentType, $questions, AssessmentGrader $grader): AssessmentAttempt
    {
        $payload = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $graded = $grader->grade($questions, $payload['answers']);

        return DB::transaction(function () use ($request, $course, $lesson, $assessmentType, $graded): AssessmentAttempt {
            $attempt = AssessmentAttempt::create([
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
                'lesson_id' => $lesson?->id,
                'assessment_type' => $assessmentType,
                'score' => $graded['score'],
                'max_score' => $graded['max_score'],
                'percentage' => $graded['percentage'],
                'passed' => $graded['passed'],
                'submitted_at' => now(),
            ]);

            foreach ($graded['answers'] as $answer) {
                $attempt->answers()->create([
                    'assessment_question_id' => $answer['question_id'],
                    'answer' => $answer['answer'],
                    'correct_answer' => $answer['correct_answer'],
                    'is_correct' => $answer['is_correct'],
                    'score' => $answer['score'],
                    'feedback' => $answer['feedback'],
                ]);
            }

            return $attempt;
        });
    }

    private function publicQuestions($questions): array
    {
        return $questions->map(function (AssessmentQuestion $question) {
            return [
                'id' => $question->id,
                'assessment_type' => $question->assessment_type,
                'question_type' => $question->question_type,
                'question' => $question->question,
                'points' => $question->points,
                'order' => $question->order,
                'options' => $question->options->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'value' => $option->value,
                    'order' => $option->order,
                    'match_value' => $question->question_type === AssessmentQuestion::TYPE_MATCHING ? null : $option->match_value,
                ])->values()->all(),
                'matching_choices' => $question->question_type === AssessmentQuestion::TYPE_MATCHING
                    ? $question->options->pluck('match_value')->filter()->shuffle()->values()->all()
                    : [],
            ];
        })->values()->all();
    }

    private function attemptPayload(?AssessmentAttempt $attempt, bool $showAnswers): ?array
    {
        if (! $attempt) {
            return null;
        }

        $attempt->load(['answers.question.options']);

        return [
            'id' => $attempt->id,
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percentage' => (float) $attempt->percentage,
            'passed' => $attempt->passed,
            'submitted_at' => $attempt->submitted_at?->toDateTimeString(),
            'show_answers' => $showAnswers,
            'answers' => $attempt->answers->map(function ($answer) use ($showAnswers) {
                return [
                    'question_id' => $answer->assessment_question_id,
                    'answer' => $answer->answer,
                    'is_correct' => $answer->is_correct,
                    'score' => $answer->score,
                    'feedback' => $answer->feedback,
                    'correct_answer' => $showAnswers ? $answer->correct_answer : null,
                    'explanation' => $showAnswers ? $answer->question?->explanation : null,
                ];
            })->values()->all(),
        ];
    }

    private function requestedAttempt(Request $request, Course $course, ?Lesson $lesson, string $assessmentType): ?AssessmentAttempt
    {
        $query = AssessmentAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->where('assessment_type', $assessmentType)
            ->when($lesson, fn ($query) => $query->where('lesson_id', $lesson->id))
            ->when(! $lesson, fn ($query) => $query->whereNull('lesson_id'));

        if ($request->filled('attempt')) {
            return (clone $query)->whereKey($request->integer('attempt'))->first();
        }

        return $query->latest('submitted_at')->first();
    }

    private function lessonQuestions(Course $course, Lesson $lesson)
    {
        return AssessmentQuestion::query()
            ->with('options')
            ->active()
            ->where('course_id', $course->id)
            ->forLessonAssessment($lesson)
            ->orderBy('order');
    }

    private function finalExamQuestions(Course $course)
    {
        return AssessmentQuestion::query()
            ->with('options')
            ->active()
            ->forFinalExam($course)
            ->orderBy('order');
    }

    private function completedLessonIds(Request $request, Course $course)
    {
        $lessonIds = $course->lessons()->published()->pluck('lessons.id');

        return $request->user()->lessonProgress()
            ->where('is_completed', true)
            ->whereIn('lesson_id', $lessonIds)
            ->pluck('lesson_id')
            ->values();
    }

    private function enrollmentOrFail(Request $request, Course $course)
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
}
