<template>
    <Head :title="title" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <LearningShell :course="course" :enrollment="enrollment" :completed-lesson-ids="completedLessonIds" :current-lesson-id="lesson?.id" :course-overview-url="backUrl" :course-completed="courseCompleted">
                    <div class="space-y-6">
                        <article class="surface-card p-6 md:p-8">
                            <Link :href="backUrl" class="text-blue-700 hover:underline">
                                {{ isArabic ? 'العودة' : 'Back' }}
                            </Link>
                            <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">
                                        {{ assessmentType === 'final_exam' ? (isArabic ? 'اختبار نهائي' : 'Final exam') : (isArabic ? 'تمرين بعد الدرس' : 'Lesson exercise') }}
                                    </p>
                                    <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">{{ title }}</h1>
                                    <p class="mt-2 text-sm leading-7 text-slate-600">
                                        {{ isArabic
                                            ? 'أجب عن الأسئلة ثم أرسل الحل. سيتم التصحيح تلقائياً وتظهر النتيجة مباشرة.'
                                            : 'Answer the questions and submit. Your answers will be graded automatically.' }}
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-5 py-4 text-center">
                                    <p class="text-2xl font-extrabold text-slate-900">{{ questions.length }}</p>
                                    <p class="text-xs text-slate-500">{{ isArabic ? 'سؤال' : 'Questions' }}</p>
                                </div>
                            </div>
                        </article>

                        <article v-if="attempt" class="surface-card overflow-hidden">
                            <div class="border-b p-6 md:p-8" :class="attempt.passed ? 'bg-emerald-50' : 'bg-orange-50'">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <h2 class="text-2xl font-extrabold text-slate-900">
                                            {{ isArabic ? 'النتيجة' : 'Result' }}: {{ attempt.score }} / {{ attempt.max_score }}
                                        </h2>
                                        <p class="mt-1 text-sm font-semibold" :class="attempt.passed ? 'text-emerald-700' : 'text-orange-700'">
                                            {{ attempt.percentage }}% — {{ attempt.passed ? (isArabic ? 'ناجح' : 'Passed') : (isArabic ? 'حاول مرة أخرى' : 'Try again') }}
                                        </p>
                                    </div>
                                    <Link
                                        v-if="showAnswersUrl && !attempt.show_answers"
                                        :href="showAnswersUrl"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 font-bold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        {{ isArabic ? 'إظهار الأجوبة الصحيحة' : 'Show correct answers' }}
                                    </Link>
                                </div>
                            </div>
                        </article>

                        <form class="space-y-5" @submit.prevent="submit">
                            <article v-for="(question, index) in questions" :key="question.id" class="surface-card p-6 md:p-8">
                                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-blue-700">
                                            {{ isArabic ? 'السؤال' : 'Question' }} {{ index + 1 }} · {{ questionTypeLabel(question.question_type) }}
                                        </p>
                                        <h2 class="mt-2 whitespace-pre-wrap text-xl font-extrabold leading-8 text-slate-900">{{ question.question }}</h2>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ question.points }} {{ isArabic ? 'نقطة' : 'pt' }}
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <template v-if="isSingleOptionQuestion(question)">
                                        <label
                                            v-for="option in question.options"
                                            :key="option.id"
                                            class="flex cursor-pointer gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-blue-200 hover:bg-blue-50/30"
                                        >
                                            <input v-model="form.answers[question.id].selected_option_id" type="radio" :name="`question-${question.id}`" :value="option.id" class="mt-1 border-slate-300 text-blue-700" />
                                            <span class="whitespace-pre-wrap text-sm font-semibold leading-7 text-slate-800">{{ option.label }}</span>
                                        </label>
                                    </template>

                                    <template v-else-if="question.question_type === 'multiple_select'">
                                        <label
                                            v-for="option in question.options"
                                            :key="option.id"
                                            class="flex cursor-pointer gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-blue-200 hover:bg-blue-50/30"
                                        >
                                            <input v-model="form.answers[question.id].selected_option_ids" type="checkbox" :value="option.id" class="mt-1 rounded border-slate-300 text-blue-700" />
                                            <span class="whitespace-pre-wrap text-sm font-semibold leading-7 text-slate-800">{{ option.label }}</span>
                                        </label>
                                    </template>

                                    <template v-else-if="question.question_type === 'ordering'">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <p class="mb-3 text-sm text-slate-500">
                                                {{ isArabic ? 'رتّب العناصر بالأسهم حتى يصبح الترتيب صحيحاً.' : 'Use the arrows to arrange the items in the correct order.' }}
                                            </p>
                                            <div class="space-y-2">
                                                <div
                                                    v-for="(optionId, optionIndex) in form.answers[question.id].ordered_option_ids"
                                                    :key="optionId"
                                                    class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm"
                                                >
                                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-sm font-extrabold text-blue-700">{{ optionIndex + 1 }}</span>
                                                    <span class="flex-1 whitespace-pre-wrap text-sm font-semibold leading-7 text-slate-800">{{ optionLabel(question, optionId) }}</span>
                                                    <div class="flex gap-1">
                                                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold" :disabled="optionIndex === 0" @click="moveOrder(question.id, optionIndex, -1)">↑</button>
                                                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold" :disabled="optionIndex === form.answers[question.id].ordered_option_ids.length - 1" @click="moveOrder(question.id, optionIndex, 1)">↓</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template v-else-if="question.question_type === 'matching'">
                                        <div class="space-y-3">
                                            <div v-for="option in question.options" :key="option.id" class="grid gap-3 rounded-2xl border border-slate-200 p-4 md:grid-cols-2 md:items-center">
                                                <div class="font-semibold leading-7 text-slate-800">{{ option.label }}</div>
                                                <select v-model="form.answers[question.id].matches[option.id]" class="w-full rounded-lg border-slate-300">
                                                    <option value="">{{ isArabic ? 'اختر المطابقة' : 'Choose match' }}</option>
                                                    <option v-for="choice in question.matching_choices" :key="choice" :value="choice">{{ choice }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div v-if="attemptAnswer(question.id)" class="mt-5 rounded-2xl border p-4" :class="attemptAnswer(question.id).is_correct ? 'border-emerald-200 bg-emerald-50' : 'border-orange-200 bg-orange-50'">
                                    <p class="font-bold" :class="attemptAnswer(question.id).is_correct ? 'text-emerald-700' : 'text-orange-700'">
                                        {{ attemptAnswer(question.id).is_correct ? (isArabic ? 'إجابة صحيحة' : 'Correct') : (isArabic ? 'إجابة غير صحيحة' : 'Incorrect') }}
                                        — {{ attemptAnswer(question.id).score }} / {{ question.points }}
                                    </p>
                                    <div v-if="attempt.show_answers && attemptAnswer(question.id).correct_answer" class="mt-3 text-sm leading-7 text-slate-700">
                                        <p class="font-bold">{{ isArabic ? 'الإجابة الصحيحة:' : 'Correct answer:' }}</p>
                                        <div class="mt-1 whitespace-pre-wrap">{{ correctAnswerText(question, attemptAnswer(question.id).correct_answer) }}</div>
                                        <p v-if="attemptAnswer(question.id).explanation" class="mt-3 border-t border-slate-200 pt-3 text-slate-600">
                                            {{ attemptAnswer(question.id).explanation }}
                                        </p>
                                    </div>
                                </div>
                            </article>

                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <Link :href="backUrl" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700 transition hover:bg-slate-50">
                                    {{ isArabic ? 'رجوع' : 'Back' }}
                                </Link>
                                <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-6 py-3 font-bold text-white transition hover:bg-blue-800" :disabled="form.processing">
                                    {{ attempt ? (isArabic ? 'إعادة المحاولة' : 'Try again') : (isArabic ? 'إرسال الإجابات' : 'Submit answers') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </LearningShell>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import LearningShell from '@/Components/LearningShell.vue';

const props = defineProps({
    course: Object,
    lesson: Object,
    enrollment: Object,
    completedLessonIds: { type: Array, default: () => [] },
    courseCompleted: { type: Boolean, default: false },
    assessmentType: String,
    title: String,
    questions: { type: Array, default: () => [] },
    attempt: Object,
    submitUrl: String,
    backUrl: String,
    showAnswersUrl: String,
});

const page = usePage();
const isArabic = computed(() => page.props.locale.current === 'ar');

const answerDefaults = Object.fromEntries(props.questions.map((question) => {
    if (isSingleOptionQuestion(question)) {
        return [question.id, { selected_option_id: null }];
    }

    if (question.question_type === 'multiple_select') {
        return [question.id, { selected_option_ids: [] }];
    }

    if (question.question_type === 'ordering') {
        return [question.id, { ordered_option_ids: question.options.map((option) => option.id) }];
    }

    if (question.question_type === 'matching') {
        return [question.id, { matches: Object.fromEntries(question.options.map((option) => [option.id, ''])) }];
    }

    return [question.id, {}];
}));

const form = useForm({ answers: answerDefaults });
const attemptAnswersByQuestion = computed(() => Object.fromEntries((props.attempt?.answers || []).map((answer) => [answer.question_id, answer])));
const attemptAnswer = (questionId) => attemptAnswersByQuestion.value[questionId] || null;

function isSingleOptionQuestion(question) {
    return ['single_choice', 'true_false', 'fill_blank', 'code_choice'].includes(question.question_type);
}

const submit = () => form.post(props.submitUrl, { preserveScroll: true });

const questionTypeLabel = (type) => ({
    single_choice: isArabic.value ? 'اختيار واحد' : 'Single choice',
    true_false: isArabic.value ? 'صح/خطأ' : 'True/false',
    multiple_select: isArabic.value ? 'اختيار متعدد' : 'Multiple select',
    ordering: isArabic.value ? 'ترتيب' : 'Ordering',
    matching: isArabic.value ? 'مطابقة' : 'Matching',
    fill_blank: isArabic.value ? 'ملء فراغ' : 'Fill blank',
    code_choice: isArabic.value ? 'اختيار الكود الصحيح' : 'Code choice',
}[type] || type);

const optionLabel = (question, optionId) => question.options.find((option) => Number(option.id) === Number(optionId))?.label || '';

const moveOrder = (questionId, index, direction) => {
    const items = [...form.answers[questionId].ordered_option_ids];
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= items.length) return;
    [items[index], items[newIndex]] = [items[newIndex], items[index]];
    form.answers[questionId].ordered_option_ids = items;
};

const correctAnswerText = (question, correctAnswer) => {
    if (['single_choice', 'true_false', 'fill_blank', 'code_choice'].includes(question.question_type)) {
        return correctAnswer.selected_label || '';
    }

    if (question.question_type === 'multiple_select') {
        return (correctAnswer.selected_labels || []).join('\n');
    }

    if (question.question_type === 'ordering') {
        return (correctAnswer.ordered_labels || []).map((label, index) => `${index + 1}. ${label}`).join('\n');
    }

    if (question.question_type === 'matching') {
        return (correctAnswer.pairs || []).map((pair) => `${pair.left} → ${pair.right}`).join('\n');
    }

    return '';
};
</script>
