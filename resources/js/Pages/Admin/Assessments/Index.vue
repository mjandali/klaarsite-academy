<template>
    <Head :title="isArabic ? 'تمارين واختبارات الكورس' : 'Course Assessments'" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container space-y-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <Link :href="`/admin/courses/${course.id}/edit`" class="text-blue-700 hover:underline">
                            {{ isArabic ? 'العودة إلى بناء الكورس' : 'Back to course builder' }}
                        </Link>
                        <h1 class="mt-3 text-4xl font-extrabold">
                            {{ isArabic ? 'التمارين والاختبار النهائي' : 'Exercises & Final Exam' }}
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                            {{ isArabic
                                ? 'أضف أسئلة بعد كل درس، أو أسئلة اختبار نهائي في نهاية الكورس. كل الأنواع هنا تُصحّح تلقائياً بدون مراجعة يدوية.'
                                : 'Add lesson exercises or final exam questions. Every question type here is graded automatically without manual review.' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white px-5 py-4 shadow-sm">
                        <p class="text-sm text-slate-500">{{ course.title }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ questions.length }}</p>
                        <p class="text-xs text-slate-500">{{ isArabic ? 'سؤال' : 'Questions' }}</p>
                    </div>
                </div>

                <div class="grid gap-8 xl:grid-cols-[minmax(0,430px)_minmax(0,1fr)]">
                    <form class="surface-card space-y-5 p-6" @submit.prevent="submitQuestion">
                        <div>
                            <h2 class="text-2xl font-extrabold">
                                {{ editingQuestionId ? (isArabic ? 'تعديل السؤال' : 'Edit Question') : (isArabic ? 'إضافة سؤال جديد' : 'Add New Question') }}
                            </h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                {{ isArabic ? 'اختر نوع السؤال ثم املأ الخيارات المناسبة.' : 'Choose the question type and fill the matching options.' }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ isArabic ? 'مكان السؤال' : 'Assessment placement' }}</label>
                            <select v-model="form.assessment_type" class="w-full rounded-lg border-slate-300" @change="handlePlacementChange">
                                <option value="lesson">{{ isArabic ? 'تمرين بعد درس' : 'Lesson exercise' }}</option>
                                <option value="final_exam">{{ isArabic ? 'الاختبار النهائي' : 'Final exam' }}</option>
                            </select>
                            <p v-if="form.errors.assessment_type" class="mt-1 text-sm text-red-600">{{ form.errors.assessment_type }}</p>
                        </div>

                        <div v-if="form.assessment_type === 'lesson'">
                            <label class="mb-2 block font-bold">{{ isArabic ? 'الدرس' : 'Lesson' }}</label>
                            <select v-model="form.lesson_id" class="w-full rounded-lg border-slate-300">
                                <option value="">{{ isArabic ? 'اختر درساً' : 'Choose a lesson' }}</option>
                                <option v-for="lesson in allLessons" :key="lesson.id" :value="lesson.id">
                                    {{ lesson.title }}
                                </option>
                            </select>
                            <p v-if="form.errors.lesson_id" class="mt-1 text-sm text-red-600">{{ form.errors.lesson_id }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ isArabic ? 'نوع السؤال' : 'Question type' }}</label>
                            <select v-model="form.question_type" class="w-full rounded-lg border-slate-300" @change="resetOptionsForType">
                                <option v-for="type in questionTypes" :key="type" :value="type">{{ questionTypeLabel(type) }}</option>
                            </select>
                            <p v-if="form.errors.question_type" class="mt-1 text-sm text-red-600">{{ form.errors.question_type }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ isArabic ? 'السؤال' : 'Question' }}</label>
                            <textarea v-model="form.question" rows="4" class="w-full rounded-lg border-slate-300" :placeholder="questionPlaceholder"></textarea>
                            <p v-if="form.errors.question" class="mt-1 text-sm text-red-600">{{ form.errors.question }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block font-bold">{{ isArabic ? 'النقاط' : 'Points' }}</label>
                                <input v-model="form.points" type="number" min="1" max="100" class="w-full rounded-lg border-slate-300" />
                            </div>
                            <div>
                                <label class="mb-2 block font-bold">{{ isArabic ? 'الترتيب' : 'Order' }}</label>
                                <input v-model="form.order" type="number" min="0" class="w-full rounded-lg border-slate-300" :placeholder="isArabic ? 'تلقائي' : 'Auto'" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ isArabic ? 'شرح الجواب الصحيح' : 'Correct answer explanation' }}</label>
                            <textarea v-model="form.explanation" rows="3" class="w-full rounded-lg border-slate-300"></textarea>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold">{{ optionSectionTitle }}</h3>
                                    <p class="text-xs leading-5 text-slate-500">{{ optionHelpText }}</p>
                                </div>
                                <button
                                    v-if="canAddOption"
                                    type="button"
                                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                    @click="addOption"
                                >
                                    {{ isArabic ? 'إضافة' : 'Add' }}
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(option, index) in form.options" :key="index" class="rounded-2xl bg-white p-3 shadow-sm">
                                    <div class="grid gap-3" :class="form.question_type === 'matching' ? 'md:grid-cols-2' : ''">
                                        <div>
                                            <label class="mb-1 block text-xs font-bold text-slate-500">{{ optionLabel }}</label>
                                            <textarea v-model="option.label" rows="2" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                                        </div>

                                        <div v-if="form.question_type === 'matching'">
                                            <label class="mb-1 block text-xs font-bold text-slate-500">{{ isArabic ? 'الإجابة المطابقة الصحيحة' : 'Correct matching answer' }}</label>
                                            <textarea v-model="option.match_value" rows="2" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                        <label v-if="usesCorrectCheckbox" class="inline-flex items-center gap-2 font-semibold text-slate-700">
                                            <input v-model="option.is_correct" type="checkbox" class="rounded border-slate-300 text-blue-700" :disabled="singleCorrectOnly && option.is_correct" @change="handleCorrectChange(index)" />
                                            {{ isArabic ? 'صحيح' : 'Correct' }}
                                        </label>

                                        <label v-if="form.question_type === 'ordering'" class="inline-flex items-center gap-2 font-semibold text-slate-700">
                                            {{ isArabic ? 'ترتيبه الصحيح' : 'Correct order' }}
                                            <input v-model="option.correct_order" type="number" min="1" class="w-24 rounded-lg border-slate-300 text-sm" />
                                        </label>

                                        <button
                                            v-if="canRemoveOption"
                                            type="button"
                                            class="ms-auto rounded-xl border border-red-200 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50"
                                            @click="removeOption(index)"
                                        >
                                            {{ isArabic ? 'حذف' : 'Remove' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.options" class="mt-2 text-sm text-red-600">{{ form.errors.options }}</p>
                        </div>

                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-blue-700" />
                            {{ isArabic ? 'السؤال فعال ويظهر للطلاب' : 'Active and visible to students' }}
                        </label>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button
                                v-if="editingQuestionId"
                                type="button"
                                class="rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700 hover:bg-slate-50"
                                @click="cancelEdit"
                            >
                                {{ isArabic ? 'إلغاء' : 'Cancel' }}
                            </button>
                            <button class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white hover:bg-blue-800" :disabled="form.processing">
                                {{ editingQuestionId ? (isArabic ? 'تحديث السؤال' : 'Update Question') : (isArabic ? 'حفظ السؤال' : 'Save Question') }}
                            </button>
                        </div>
                    </form>

                    <section class="space-y-6">
                        <article class="surface-card p-6">
                            <h2 class="text-2xl font-extrabold">{{ isArabic ? 'الاختبار النهائي' : 'Final Exam' }}</h2>
                            <QuestionList
                                :items="finalExamQuestions"
                                :is-arabic="isArabic"
                                @edit="startEdit"
                                @delete="deleteQuestion"
                            />
                        </article>

                        <article v-for="lesson in allLessons" :key="lesson.id" class="surface-card p-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <h2 class="text-xl font-extrabold">{{ lesson.title }}</h2>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    {{ lessonQuestions(lesson.id).length }} {{ isArabic ? 'سؤال' : 'questions' }}
                                </span>
                            </div>
                            <QuestionList
                                :items="lessonQuestions(lesson.id)"
                                :is-arabic="isArabic"
                                @edit="startEdit"
                                @delete="deleteQuestion"
                            />
                        </article>
                    </section>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed, h, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    course: { type: Object, required: true },
    questions: { type: Array, default: () => [] },
    questionTypes: { type: Array, default: () => [] },
});

const page = usePage();
const { confirmDestructive } = useConfirm();
const isArabic = computed(() => page.props.locale.current === 'ar');
const allLessons = computed(() => props.course.sections.flatMap((section) => section.lessons || []));
const finalExamQuestions = computed(() => props.questions.filter((question) => question.assessment_type === 'final_exam'));
const lessonQuestions = (lessonId) => props.questions.filter((question) => question.assessment_type === 'lesson' && Number(question.lesson_id) === Number(lessonId));

const optionFactory = (overrides = {}) => ({
    label: '',
    value: '',
    is_correct: false,
    correct_order: null,
    match_value: '',
    order: null,
    ...overrides,
});

const defaultOptionsFor = (type) => {
    if (type === 'true_false') {
        return [optionFactory({ label: isArabic.value ? 'صح' : 'True', is_correct: true, order: 1 }), optionFactory({ label: isArabic.value ? 'خطأ' : 'False', order: 2 })];
    }

    if (type === 'ordering') {
        return [optionFactory({ label: '', correct_order: 1, order: 1 }), optionFactory({ label: '', correct_order: 2, order: 2 })];
    }

    if (type === 'matching') {
        return [optionFactory({ label: '', match_value: '', order: 1 }), optionFactory({ label: '', match_value: '', order: 2 })];
    }

    return [optionFactory({ is_correct: true, order: 1 }), optionFactory({ order: 2 })];
};

const form = useForm({
    assessment_type: 'lesson',
    lesson_id: allLessons.value[0]?.id || '',
    question_type: 'single_choice',
    question: '',
    explanation: '',
    points: 1,
    order: '',
    is_active: true,
    options: defaultOptionsFor('single_choice'),
});

const editingQuestionId = ref(null);

const questionTypeLabel = (type) => ({
    single_choice: isArabic.value ? 'اختيار من متعدد - إجابة واحدة' : 'Multiple choice - one answer',
    true_false: isArabic.value ? 'صح / خطأ' : 'True / False',
    multiple_select: isArabic.value ? 'اختيار متعدد - أكثر من إجابة' : 'Multiple select',
    ordering: isArabic.value ? 'ترتيب الخطوات' : 'Ordering steps',
    matching: isArabic.value ? 'مطابقة' : 'Matching',
    fill_blank: isArabic.value ? 'ملء فراغ من اختيارات' : 'Fill blank from choices',
    code_choice: isArabic.value ? 'اختيار الكود الصحيح' : 'Choose correct code',
}[type] || type);

const questionPlaceholder = computed(() => {
    if (form.question_type === 'fill_blank') {
        return isArabic.value ? 'اكتب السؤال وضع ____ مكان الفراغ' : 'Write the question and use ____ for the blank';
    }

    if (form.question_type === 'code_choice') {
        return isArabic.value ? 'أي سطر كود صحيح لإنجاز المهمة التالية؟' : 'Which code snippet correctly solves this task?';
    }

    return isArabic.value ? 'اكتب نص السؤال هنا' : 'Write the question here';
});

const optionSectionTitle = computed(() => {
    if (form.question_type === 'ordering') return isArabic.value ? 'الخطوات بالترتيب الصحيح' : 'Steps in the correct order';
    if (form.question_type === 'matching') return isArabic.value ? 'أزواج المطابقة' : 'Matching pairs';
    return isArabic.value ? 'الخيارات' : 'Options';
});

const optionHelpText = computed(() => {
    if (form.question_type === 'ordering') return isArabic.value ? 'أدخل الخطوات حسب الترتيب الصحيح.' : 'Enter steps in the correct order.';
    if (form.question_type === 'matching') return isArabic.value ? 'اكتب العنصر في اليسار والإجابة المطابقة الصحيحة في اليمين.' : 'Write the left item and its correct matching answer.';
    if (form.question_type === 'multiple_select') return isArabic.value ? 'يمكن تحديد أكثر من خيار صحيح.' : 'You may mark more than one correct option.';
    return isArabic.value ? 'حدد الخيار الصحيح.' : 'Mark the correct option.';
});

const optionLabel = computed(() => {
    if (form.question_type === 'ordering') return isArabic.value ? 'الخطوة' : 'Step';
    if (form.question_type === 'matching') return isArabic.value ? 'العنصر' : 'Item';
    if (form.question_type === 'code_choice') return isArabic.value ? 'كود / خيار' : 'Code / option';
    return isArabic.value ? 'الخيار' : 'Option';
});

const usesCorrectCheckbox = computed(() => ['single_choice', 'true_false', 'multiple_select', 'fill_blank', 'code_choice'].includes(form.question_type));
const singleCorrectOnly = computed(() => ['single_choice', 'true_false', 'fill_blank', 'code_choice'].includes(form.question_type));
const canAddOption = computed(() => form.question_type !== 'true_false');
const canRemoveOption = computed(() => form.options.length > (form.question_type === 'true_false' ? 2 : 2));

const handlePlacementChange = () => {
    if (form.assessment_type === 'final_exam') {
        form.lesson_id = '';
    } else if (!form.lesson_id) {
        form.lesson_id = allLessons.value[0]?.id || '';
    }
};

const resetOptionsForType = () => {
    form.options = defaultOptionsFor(form.question_type);
};

const addOption = () => form.options.push(optionFactory({ order: form.options.length + 1, correct_order: form.question_type === 'ordering' ? form.options.length + 1 : null }));
const removeOption = (index) => form.options.splice(index, 1);

const handleCorrectChange = (index) => {
    if (!singleCorrectOnly.value || !form.options[index].is_correct) return;
    form.options = form.options.map((option, optionIndex) => ({ ...option, is_correct: optionIndex === index }));
};

const payload = () => ({
    ...form.data(),
    order: form.order === '' ? null : form.order,
    lesson_id: form.assessment_type === 'lesson' ? form.lesson_id : null,
    options: form.options.map((option, index) => ({
        ...option,
        order: option.order || index + 1,
        correct_order: form.question_type === 'ordering' ? (option.correct_order || index + 1) : null,
    })),
});

const submitQuestion = () => {
    const options = { preserveScroll: true, onSuccess: cancelEdit };
    if (editingQuestionId.value) {
        router.put(`/admin/courses/${props.course.id}/assessments/questions/${editingQuestionId.value}`, payload(), options);
    } else {
        router.post(`/admin/courses/${props.course.id}/assessments/questions`, payload(), options);
    }
};

const startEdit = (question) => {
    editingQuestionId.value = question.id;
    form.clearErrors();
    form.assessment_type = question.assessment_type;
    form.lesson_id = question.lesson_id || '';
    form.question_type = question.question_type;
    form.question = question.question;
    form.explanation = question.explanation || '';
    form.points = question.points || 1;
    form.order = question.order ?? '';
    form.is_active = Boolean(question.is_active);
    form.options = (question.options || []).map((option) => optionFactory({
        label: option.label,
        value: option.value || '',
        is_correct: Boolean(option.is_correct),
        correct_order: option.correct_order,
        match_value: option.match_value || '',
        order: option.order,
    }));
};

const cancelEdit = () => {
    editingQuestionId.value = null;
    form.clearErrors();
    form.reset();
    form.assessment_type = 'lesson';
    form.lesson_id = allLessons.value[0]?.id || '';
    form.question_type = 'single_choice';
    form.question = '';
    form.explanation = '';
    form.points = 1;
    form.order = '';
    form.is_active = true;
    form.options = defaultOptionsFor('single_choice');
};

const deleteQuestion = async (question) => {
    const confirmed = await confirmDestructive({
        title: isArabic.value ? 'حذف السؤال؟' : 'Delete question?',
        text: isArabic.value ? 'سيتم حذف السؤال ومحاولات الإجابة المرتبطة به.' : 'The question and related submitted answers will be deleted.',
        confirmButtonText: isArabic.value ? 'حذف' : 'Delete',
    });
    if (!confirmed) return;
    router.delete(`/admin/courses/${props.course.id}/assessments/questions/${question.id}`, { preserveScroll: true });
};
const QuestionList = {
    props: {
        items: { type: Array, default: () => [] },
        isArabic: { type: Boolean, default: false },
    },
    emits: ['edit', 'delete'],
    setup(props, { emit }) {
        const label = (type) => ({
            single_choice: props.isArabic ? 'اختيار واحد' : 'Single choice',
            true_false: props.isArabic ? 'صح/خطأ' : 'True/false',
            multiple_select: props.isArabic ? 'اختيار متعدد' : 'Multiple select',
            ordering: props.isArabic ? 'ترتيب' : 'Ordering',
            matching: props.isArabic ? 'مطابقة' : 'Matching',
            fill_blank: props.isArabic ? 'ملء فراغ' : 'Fill blank',
            code_choice: props.isArabic ? 'كود صحيح' : 'Code choice',
        }[type] || type);

        return () => props.items.length
            ? h('div', { class: 'mt-5 space-y-3' }, props.items.map((question) => h('div', { class: 'rounded-2xl border border-slate-200 bg-slate-50 p-4', key: question.id }, [
                h('div', { class: 'flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between' }, [
                    h('div', { class: 'min-w-0' }, [
                        h('div', { class: 'mb-2 flex flex-wrap gap-2 text-xs' }, [
                            h('span', { class: 'rounded-full bg-blue-50 px-2 py-1 font-bold text-blue-700' }, label(question.question_type)),
                            h('span', { class: question.is_active ? 'rounded-full bg-emerald-50 px-2 py-1 font-bold text-emerald-700' : 'rounded-full bg-slate-200 px-2 py-1 font-bold text-slate-600' }, question.is_active ? (props.isArabic ? 'فعال' : 'Active') : (props.isArabic ? 'مخفي' : 'Hidden')),
                            h('span', { class: 'rounded-full bg-white px-2 py-1 font-bold text-slate-600' }, `${question.points} ${props.isArabic ? 'نقطة' : 'pt'}`),
                        ]),
                        h('p', { class: 'font-bold leading-7 text-slate-900' }, question.question),
                        question.options?.length ? h('p', { class: 'mt-1 text-xs text-slate-500' }, `${question.options.length} ${props.isArabic ? 'خيار/عنصر' : 'options/items'}`) : null,
                    ]),
                    h('div', { class: 'flex shrink-0 flex-wrap gap-2' }, [
                        h('button', { type: 'button', class: 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50', onClick: () => emit('edit', question) }, props.isArabic ? 'تعديل' : 'Edit'),
                        h('button', { type: 'button', class: 'rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50', onClick: () => emit('delete', question) }, props.isArabic ? 'حذف' : 'Delete'),
                    ]),
                ]),
            ])))
            : h('div', { class: 'mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500' }, props.isArabic ? 'لا توجد أسئلة بعد.' : 'No questions yet.');
    },
};
</script>
