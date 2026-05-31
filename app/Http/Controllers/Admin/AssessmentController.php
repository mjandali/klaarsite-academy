<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AssessmentController extends Controller
{
    public function index(Course $course): InertiaResponse
    {
        $this->authorize('update', $course);

        $course->load([
            'sections' => fn ($query) => $query->orderBy('order'),
            'sections.lessons' => fn ($query) => $query->orderBy('order'),
        ]);

        $questions = AssessmentQuestion::query()
            ->with(['lesson:id,title,course_section_id', 'options'])
            ->where('course_id', $course->id)
            ->orderByRaw("case when assessment_type = 'final_exam' then 1 else 0 end")
            ->orderBy('lesson_id')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Assessments/Index', [
            'course' => $course,
            'questions' => $questions,
            'questionTypes' => AssessmentQuestion::QUESTION_TYPES,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $this->validatedQuestion($request, $course);

        DB::transaction(function () use ($data, $course): void {
            $question = AssessmentQuestion::create([
                'course_id' => $course->id,
                'lesson_id' => $data['assessment_type'] === AssessmentQuestion::ASSESSMENT_LESSON ? $data['lesson_id'] : null,
                'assessment_type' => $data['assessment_type'],
                'question_type' => $data['question_type'],
                'question' => $data['question'],
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'] ?? 1,
                'order' => $data['order'] ?? $this->nextQuestionOrder($course, $data),
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncOptions($question, $data['options'] ?? []);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حفظ السؤال بنجاح.' : 'Question saved successfully.');
    }

    public function update(Request $request, Course $course, AssessmentQuestion $question): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($question->course_id === $course->id, 404);

        $data = $this->validatedQuestion($request, $course);

        DB::transaction(function () use ($data, $question): void {
            $question->update([
                'lesson_id' => $data['assessment_type'] === AssessmentQuestion::ASSESSMENT_LESSON ? $data['lesson_id'] : null,
                'assessment_type' => $data['assessment_type'],
                'question_type' => $data['question_type'],
                'question' => $data['question'],
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'] ?? 1,
                'order' => $data['order'] ?? $question->order,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncOptions($question, $data['options'] ?? []);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث السؤال بنجاح.' : 'Question updated successfully.');
    }

    public function destroy(Course $course, AssessmentQuestion $question): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($question->course_id === $course->id, 404);

        $question->delete();

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف السؤال.' : 'Question deleted.');
    }

    private function validatedQuestion(Request $request, Course $course): array
    {
        $data = $request->validate([
            'assessment_type' => ['required', Rule::in([AssessmentQuestion::ASSESSMENT_LESSON, AssessmentQuestion::ASSESSMENT_FINAL_EXAM])],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'question_type' => ['required', Rule::in(AssessmentQuestion::QUESTION_TYPES)],
            'question' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['boolean'],
            'options' => ['required', 'array', 'min:1', 'max:20'],
            'options.*.label' => ['required', 'string', 'max:5000'],
            'options.*.value' => ['nullable', 'string', 'max:5000'],
            'options.*.is_correct' => ['boolean'],
            'options.*.correct_order' => ['nullable', 'integer', 'min:1', 'max:100'],
            'options.*.match_value' => ['nullable', 'string', 'max:5000'],
            'options.*.order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        if ($data['assessment_type'] === AssessmentQuestion::ASSESSMENT_LESSON) {
            if (empty($data['lesson_id'])) {
                throw ValidationException::withMessages([
                    'lesson_id' => app()->getLocale() === 'ar' ? 'اختر الدرس المرتبط بالتمرين.' : 'Choose the lesson for this exercise.',
                ]);
            }

            $belongsToCourse = Lesson::query()
                ->whereKey($data['lesson_id'])
                ->whereHas('section', fn ($query) => $query->where('course_id', $course->id))
                ->exists();

            abort_unless($belongsToCourse, 404);
        }

        if ($data['assessment_type'] === AssessmentQuestion::ASSESSMENT_FINAL_EXAM) {
            $data['lesson_id'] = null;
        }

        $this->validateQuestionOptions($data['question_type'], $data['options']);

        return $data;
    }

    private function validateQuestionOptions(string $type, array $options): void
    {
        $count = count($options);
        $correctCount = collect($options)->filter(fn ($option) => (bool) ($option['is_correct'] ?? false))->count();

        if (in_array($type, [AssessmentQuestion::TYPE_SINGLE_CHOICE, AssessmentQuestion::TYPE_CODE_CHOICE, AssessmentQuestion::TYPE_FILL_BLANK], true)) {
            if ($count < 2 || $correctCount !== 1) {
                throw ValidationException::withMessages([
                    'options' => app()->getLocale() === 'ar'
                        ? 'هذا النوع يحتاج خيارين على الأقل وخياراً صحيحاً واحداً فقط.'
                        : 'This type needs at least two options and exactly one correct option.',
                ]);
            }
        }

        if ($type === AssessmentQuestion::TYPE_TRUE_FALSE && ($count !== 2 || $correctCount !== 1)) {
            throw ValidationException::withMessages([
                'options' => app()->getLocale() === 'ar'
                    ? 'سؤال صح/خطأ يحتاج خيارين فقط وخياراً صحيحاً واحداً.'
                    : 'True/false questions need exactly two options and one correct option.',
            ]);
        }

        if ($type === AssessmentQuestion::TYPE_MULTIPLE_SELECT && ($count < 2 || $correctCount < 1)) {
            throw ValidationException::withMessages([
                'options' => app()->getLocale() === 'ar'
                    ? 'اختيار متعدد يحتاج خيارين على الأقل وخياراً صحيحاً واحداً أو أكثر.'
                    : 'Multiple select needs at least two options and one or more correct options.',
            ]);
        }

        if ($type === AssessmentQuestion::TYPE_ORDERING && $count < 2) {
            throw ValidationException::withMessages([
                'options' => app()->getLocale() === 'ar'
                    ? 'ترتيب الخطوات يحتاج خطوتين على الأقل.'
                    : 'Ordering questions need at least two steps.',
            ]);
        }

        if ($type === AssessmentQuestion::TYPE_MATCHING) {
            $missingPairs = collect($options)->contains(fn ($option) => blank($option['match_value'] ?? null));
            if ($count < 2 || $missingPairs) {
                throw ValidationException::withMessages([
                    'options' => app()->getLocale() === 'ar'
                        ? 'المطابقة تحتاج عنصرين على الأقل ولكل عنصر إجابة مطابقة.'
                        : 'Matching questions need at least two items, each with a matching answer.',
                ]);
            }
        }
    }

    private function syncOptions(AssessmentQuestion $question, array $options): void
    {
        $question->options()->delete();

        foreach (array_values($options) as $index => $option) {
            $question->options()->create([
                'label' => $option['label'],
                'value' => $option['value'] ?? null,
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'correct_order' => $question->question_type === AssessmentQuestion::TYPE_ORDERING
                    ? (int) ($option['correct_order'] ?? $index + 1)
                    : null,
                'match_value' => $question->question_type === AssessmentQuestion::TYPE_MATCHING
                    ? ($option['match_value'] ?? null)
                    : null,
                'order' => (int) ($option['order'] ?? $index + 1),
            ]);
        }
    }

    private function nextQuestionOrder(Course $course, array $data): int
    {
        return ((int) AssessmentQuestion::query()
            ->where('course_id', $course->id)
            ->where('assessment_type', $data['assessment_type'])
            ->when($data['assessment_type'] === AssessmentQuestion::ASSESSMENT_LESSON, fn ($query) => $query->where('lesson_id', $data['lesson_id']))
            ->when($data['assessment_type'] === AssessmentQuestion::ASSESSMENT_FINAL_EXAM, fn ($query) => $query->whereNull('lesson_id'))
            ->max('order')) + 1;
    }
}
