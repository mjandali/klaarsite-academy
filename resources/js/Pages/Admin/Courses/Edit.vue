<template>
    <Head :title="course.title" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container space-y-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <Link href="/admin/courses" class="text-blue-700 hover:underline">{{ isArabic ? 'العودة إلى الكورسات' : 'Back to courses' }}</Link>
                        <h1 class="mt-3 text-4xl font-extrabold">{{ course.title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                            {{ isArabic ? 'هذه هي صفحة بناء الكورس الموحدة: عدّل التفاصيل، ثم نظّم الأقسام والدروس، وعاين أي درس قبل نشره.' : 'This is the single course builder page: edit the course details, organize sections and lessons, and preview any lesson before publishing.' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <span class="rounded-full px-3 py-2 text-sm font-bold uppercase tracking-wide" :class="courseStatusClass(courseForm.status)">
                            {{ courseStatusLabel(courseForm.status) }}
                        </span>
                        <span class="rounded-full bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
                            {{ formatLabel(course.course_format) }}
                        </span>
                        <Link
                            v-if="course.status === 'published'"
                            :href="`/courses/${course.slug}`"
                            class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 font-bold text-white transition hover:bg-slate-800"
                        >
                            {{ t('nav.view_site') }}
                        </Link>
                    </div>
                </div>

                <div class="grid gap-8 xl:grid-cols-[minmax(0,390px)_minmax(0,1fr)]">
                    <CourseForm :form="courseForm" :submit-label="isArabic ? 'حفظ الكورس' : 'Save Course'" @submit="submitCourse" />

                    <section class="surface-card p-6 sm:p-7">
                        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h2 class="text-2xl font-extrabold">{{ isArabic ? 'مخطط الكورس' : 'Course Builder' }}</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ isArabic ? 'رتّب الأقسام والدروس بالأسهم، وافتح محرر الدرس من داخل نفس الصفحة.' : 'Reorder sections and lessons with the arrow controls, and edit lessons inline from this page.' }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3 text-sm">
                                <div class="rounded-2xl bg-slate-100 px-4 py-3">
                                    <p class="font-bold">{{ course.sections.length }}</p>
                                    <p class="text-slate-500">{{ t('admin.sections') }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-100 px-4 py-3">
                                    <p class="font-bold">{{ course.lessons_count }}</p>
                                    <p class="text-slate-500">{{ t('admin.lessons') }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-100 px-4 py-3">
                                    <p class="font-bold">{{ course.enrollments_count }}</p>
                                    <p class="text-slate-500">{{ t('admin.students') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-extrabold">{{ isArabic ? 'إضافة قسم جديد' : 'Add New Section' }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ isArabic ? 'ابدأ ببنية واضحة للكورس. يمكنك تعديل ترتيب الأقسام لاحقاً من الأسهم.' : 'Start with a clear structure. You can reorder sections later with the up/down controls.' }}
                                    </p>
                                </div>
                            </div>

                            <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="addSection">
                                <div class="md:col-span-2">
                                    <label class="mb-2 block font-bold">{{ isArabic ? 'عنوان القسم' : 'Section Title' }}</label>
                                    <input v-model="sectionForm.title" class="w-full rounded-lg border-slate-300" />
                                    <p v-if="sectionForm.errors.title" class="mt-1 text-sm text-red-600">{{ sectionForm.errors.title }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-2 block font-bold">{{ isArabic ? 'وصف القسم' : 'Section Description' }}</label>
                                    <textarea v-model="sectionForm.description" rows="3" class="w-full rounded-lg border-slate-300"></textarea>
                                    <p v-if="sectionForm.errors.description" class="mt-1 text-sm text-red-600">{{ sectionForm.errors.description }}</p>
                                </div>

                                <div class="md:col-span-2 flex justify-end">
                                    <button class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white transition hover:bg-blue-800" :disabled="sectionForm.processing">
                                        {{ isArabic ? 'إضافة القسم' : 'Add Section' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div v-if="!course.sections.length" class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                            <h3 class="text-xl font-extrabold text-slate-800">{{ isArabic ? 'لا يوجد محتوى داخل هذا الكورس بعد' : 'This course has no content yet' }}</h3>
                            <p class="mt-2 text-sm leading-7">
                                {{ isArabic ? 'أضف أول قسم لتبدأ بناء محتوى الكورس، وبعدها ستتمكن من إضافة الدروس وترتيبها ومعاينتها.' : 'Add the first section to start building the curriculum, then add lessons, order them, and preview them.' }}
                            </p>
                        </div>

                        <div v-else class="mt-6 space-y-5">
                            <article v-for="(section, sectionIndex) in course.sections" :key="section.id" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-col gap-4 border-b border-slate-100 pb-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white">{{ section.order }}</span>
                                            <div>
                                                <h3 class="text-xl font-extrabold">{{ section.title }}</h3>
                                                <p v-if="section.description" class="mt-1 text-sm leading-6 text-slate-500">{{ section.description }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-600">
                                                {{ section.lessons.length }} {{ isArabic ? 'دروس' : 'lessons' }}
                                            </span>
                                            <span v-if="!section.lessons.length" class="rounded-full bg-amber-50 px-2 py-1 text-amber-700">
                                                {{ isArabic ? 'قسم فارغ' : 'Empty section' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :disabled="sectionIndex === 0"
                                            @click="moveSection(section.id, 'up')"
                                        >
                                            {{ isArabic ? 'أعلى' : 'Up' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :disabled="sectionIndex === course.sections.length - 1"
                                            @click="moveSection(section.id, 'down')"
                                        >
                                            {{ isArabic ? 'أسفل' : 'Down' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="toggleSectionEdit(section)"
                                        >
                                            {{ editingSectionId === section.id ? (isArabic ? 'إغلاق' : 'Close') : t('admin.edit') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-xl bg-blue-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-blue-800"
                                            @click="startCreateLesson(section.id)"
                                        >
                                            {{ isArabic ? 'إضافة درس' : 'Add Lesson' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-xl border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                            @click="deleteSection(section.id)"
                                        >
                                            {{ t('admin.delete') }}
                                        </button>
                                    </div>
                                </div>

                                <form v-if="editingSectionId === section.id" class="mt-4 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2" @submit.prevent="updateSection">
                                    <div class="md:col-span-2">
                                        <label class="mb-2 block font-bold">{{ isArabic ? 'عنوان القسم' : 'Section Title' }}</label>
                                        <input v-model="sectionUpdateForm.title" class="w-full rounded-lg border-slate-300" />
                                        <p v-if="sectionUpdateForm.errors.title" class="mt-1 text-sm text-red-600">{{ sectionUpdateForm.errors.title }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="mb-2 block font-bold">{{ isArabic ? 'وصف القسم' : 'Section Description' }}</label>
                                        <textarea v-model="sectionUpdateForm.description" rows="3" class="w-full rounded-lg border-slate-300"></textarea>
                                        <p v-if="sectionUpdateForm.errors.description" class="mt-1 text-sm text-red-600">{{ sectionUpdateForm.errors.description }}</p>
                                    </div>
                                    <div class="flex justify-end md:col-span-2">
                                        <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white transition hover:bg-slate-800" :disabled="sectionUpdateForm.processing">
                                            {{ isArabic ? 'حفظ القسم' : 'Save Section' }}
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-5 space-y-3">
                                    <div v-if="!section.lessons.length" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-slate-500">
                                        <h4 class="font-bold text-slate-800">{{ isArabic ? 'لا توجد دروس في هذا القسم بعد' : 'No lessons in this section yet' }}</h4>
                                        <p class="mt-2 text-sm">
                                            {{ isArabic ? 'ابدأ بإضافة أول درس كتابي أو مرئي أو مختلط.' : 'Start by adding the first written, video, or mixed lesson.' }}
                                        </p>
                                    </div>

                                    <div v-for="(lesson, lessonIndex) in section.lessons" :key="lesson.id" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="rounded-full bg-blue-700 px-2.5 py-1 text-xs font-bold text-white">{{ lesson.order }}</span>
                                                    <h4 class="text-lg font-extrabold">{{ lesson.title }}</h4>
                                                </div>
                                                <p v-if="lesson.description" class="mt-2 text-sm leading-6 text-slate-500">{{ lesson.description }}</p>
                                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="rounded-full bg-blue-50 px-2 py-1 font-semibold text-blue-700">{{ formatLabel(lesson.type) }}</span>
                                                    <span class="rounded-full px-2 py-1 font-semibold" :class="lesson.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                                                        {{ lesson.status === 'published' ? t('admin.published') : t('admin.draft') }}
                                                    </span>
                                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-600">{{ lesson.duration_minutes || 0 }} {{ isArabic ? 'دقيقة' : 'min' }}</span>
                                                    <span v-if="lesson.attachments?.length" class="rounded-full bg-slate-100 px-2 py-1 text-slate-600">
                                                        {{ lesson.attachments.length }} {{ isArabic ? 'مرفق' : 'attachments' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <Link
                                                    :href="`/admin/lessons/${lesson.id}/preview`"
                                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                                >
                                                    {{ isArabic ? 'معاينة' : 'Preview' }}
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                                    :disabled="lessonIndex === 0"
                                                    @click="moveLesson(lesson.id, 'up')"
                                                >
                                                    {{ isArabic ? 'أعلى' : 'Up' }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                                    :disabled="lessonIndex === section.lessons.length - 1"
                                                    @click="moveLesson(lesson.id, 'down')"
                                                >
                                                    {{ isArabic ? 'أسفل' : 'Down' }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                                    @click="toggleLessonEdit(lesson)"
                                                >
                                                    {{ editingLessonId === lesson.id ? (isArabic ? 'إغلاق' : 'Close') : t('admin.edit') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-xl border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                                    @click="deleteLesson(lesson.id)"
                                                >
                                                    {{ t('admin.delete') }}
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="editingLessonId === lesson.id"
                                            class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800"
                                        >
                                            {{ isArabic ? 'محرر هذا الدرس مفتوح الآن في نافذة واسعة.' : 'This lesson is open in the large editor window.' }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="creatingLessonForSectionId === section.id"
                                        class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800"
                                    >
                                        {{ isArabic ? 'نافذة إضافة درس جديد مفتوحة الآن.' : 'The add lesson window is open now.' }}
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <Teleport to="body">
            <div
                v-if="isLessonModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-3 sm:p-5"
                @click.self="closeLessonModal"
            >
                <div
                    class="flex h-[92vh] w-full max-w-[96vw] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl xl:max-w-[1400px]"
                    role="dialog"
                    aria-modal="true"
                >
                    <header class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">
                                {{ isArabic ? 'محرر الدرس' : 'Lesson Editor' }}
                            </p>
                            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">
                                {{ lessonModalTitle }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ isArabic ? 'استخدم هذه النافذة الكبيرة للتركيز على محتوى الدرس. شريط أدوات المحرر سيبقى ظاهراً أثناء التمرير.' : 'Use this large window to focus on lesson content. The editor toolbar stays visible while scrolling.' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                            @click="closeLessonModal"
                        >
                            {{ isArabic ? 'إغلاق' : 'Close' }}
                        </button>
                    </header>

                    <div class="lesson-editor-modal__body flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6">
                        <div class="mx-auto max-w-6xl rounded-3xl bg-white p-4 shadow-sm sm:p-6">
                            <LessonEditor
                                v-if="editingLessonId && activeLesson"
                                :form="lessonUpdateForm"
                                :sections="course.sections"
                                :attachments="activeLesson.attachments || []"
                                :media="activeLesson.media || []"
                                :lesson-id="activeLesson.id"
                                :title="isArabic ? 'تعديل الدرس' : 'Edit Lesson'"
                                :submit-label="isArabic ? 'حفظ الدرس' : 'Save Lesson'"
                                @submit="updateLesson"
                                @cancel="closeLessonModal"
                            />

                            <LessonEditor
                                v-else-if="creatingLessonForSectionId"
                                :form="newLessonForm"
                                :sections="course.sections"
                                :attachments="[]"
                                :media="[]"
                                :lesson-id="null"
                                :title="isArabic ? 'إضافة درس جديد' : 'Add New Lesson'"
                                :submit-label="isArabic ? 'إنشاء الدرس' : 'Create Lesson'"
                                @submit="createLesson"
                                @cancel="closeLessonModal"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourseForm from '@/Components/CourseForm.vue';
import LessonEditor from '@/Components/LessonEditor.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({
    course: { type: Object, required: true },
});

const page = usePage();
const { t } = useTranslations();
const { confirmDestructive } = useConfirm();
const isArabic = computed(() => page.props.locale.current === 'ar');

const courseForm = useForm({
    title: props.course.title,
    subtitle: props.course.subtitle || '',
    description: props.course.description || '',
    price: props.course.price,
    currency: props.course.currency || 'USD',
    level: props.course.level || 'beginner',
    duration_hours: props.course.duration_hours || '',
    meta_description: props.course.meta_description || '',
    course_format: props.course.course_format || 'mixed',
    status: props.course.status || 'draft',
    thumbnail: null,
});

const sectionForm = useForm({
    course_id: props.course.id,
    title: '',
    description: '',
    order: '',
});

const sectionUpdateForm = useForm({
    title: '',
    description: '',
    order: '',
});

const defaultLessonType = computed(() => {
    if (courseForm.course_format === 'video') {
        return 'video';
    }

    if (courseForm.course_format === 'text') {
        return 'text';
    }

    return 'mixed';
});

const makeLessonDefaults = (sectionId = '') => ({
    course_section_id: sectionId || props.course.sections[0]?.id || '',
    title: '',
    description: '',
    type: defaultLessonType.value,
    content: '',
    video_url: '',
    video_provider: null,
    video_id: null,
    duration_minutes: '',
    status: 'draft',
    order: '',
    attachments: [],
    delete_attachments: [],
});

const newLessonForm = useForm(makeLessonDefaults());
const lessonUpdateForm = useForm(makeLessonDefaults());

const editingSectionId = ref(null);
const creatingLessonForSectionId = ref(null);
const editingLessonId = ref(null);
const allLessons = computed(() => props.course.sections.flatMap((section) => section.lessons || []));
const activeLesson = computed(() => allLessons.value.find((lesson) => lesson.id === editingLessonId.value) || null);
const isLessonModalOpen = computed(() => Boolean(editingLessonId.value || creatingLessonForSectionId.value));
const lessonModalTitle = computed(() => {
    if (editingLessonId.value && activeLesson.value) {
        return activeLesson.value.title;
    }

    return isArabic.value ? 'إضافة درس جديد' : 'Add New Lesson';
});

const closeLessonModal = () => {
    if (editingLessonId.value) {
        stopEditingLesson();
        return;
    }

    stopCreatingLesson();
};

watch(isLessonModalOpen, (isOpen) => {
    document.documentElement.classList.toggle('overflow-hidden', isOpen);
});

onBeforeUnmount(() => {
    document.documentElement.classList.remove('overflow-hidden');
});

const resetLessonForm = (form, values) => {
    form.clearErrors();
    Object.entries(values).forEach(([key, value]) => {
        form[key] = value;
    });
};

const submitCourse = () => {
    courseForm
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/admin/courses/${props.course.id}`, { forceFormData: true, preserveScroll: true });
};

const addSection = () => {
    sectionForm.post('/admin/sections', {
        preserveScroll: true,
        onSuccess: () => sectionForm.reset('title', 'description', 'order'),
    });
};

const toggleSectionEdit = (section) => {
    if (editingSectionId.value === section.id) {
        editingSectionId.value = null;
        sectionUpdateForm.clearErrors();

        return;
    }

    editingSectionId.value = section.id;
    sectionUpdateForm.clearErrors();
    sectionUpdateForm.title = section.title;
    sectionUpdateForm.description = section.description || '';
    sectionUpdateForm.order = section.order;
};

const updateSection = () => {
    if (!editingSectionId.value) {
        return;
    }

    sectionUpdateForm.put(`/admin/sections/${editingSectionId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingSectionId.value = null;
        },
    });
};

const moveSection = (sectionId, direction) => {
    router.post(`/admin/sections/${sectionId}/move`, { direction }, { preserveScroll: true });
};

const deleteSection = async (sectionId) => {
    const confirmed = await confirmDestructive({
        title: isArabic.value ? 'هل تريد حذف هذا القسم؟' : 'Delete this section?',
        text: isArabic.value
            ? 'سيتم حذف القسم وكل الدروس والمرفقات التابعة له.'
            : 'The section and all nested lessons and attachments will be deleted.',
        confirmButtonText: isArabic.value ? 'حذف القسم' : 'Delete Section',
    });

    if (!confirmed) {
        return;
    }

    router.delete(`/admin/sections/${sectionId}`, { preserveScroll: true });
};

const startCreateLesson = (sectionId) => {
    creatingLessonForSectionId.value = sectionId;
    editingLessonId.value = null;
    resetLessonForm(newLessonForm, makeLessonDefaults(sectionId));
};

const stopCreatingLesson = () => {
    creatingLessonForSectionId.value = null;
    newLessonForm.clearErrors();
};

const createLesson = () => {
    newLessonForm.post('/admin/lessons', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            creatingLessonForSectionId.value = null;
            resetLessonForm(newLessonForm, makeLessonDefaults());
        },
    });
};

const toggleLessonEdit = (lesson) => {
    if (editingLessonId.value === lesson.id) {
        stopEditingLesson();

        return;
    }

    creatingLessonForSectionId.value = null;
    editingLessonId.value = lesson.id;
    resetLessonForm(lessonUpdateForm, {
        course_section_id: lesson.course_section_id,
        title: lesson.title,
        description: lesson.description || '',
        type: lesson.type,
        content: lesson.content || '',
        video_url: lesson.video_url || '',
        video_provider: lesson.video_provider || null,
        video_id: lesson.video_id || null,
        duration_minutes: lesson.duration_minutes || '',
        status: lesson.status || 'draft',
        order: lesson.order,
        attachments: [],
        delete_attachments: [],
    });
};

const stopEditingLesson = () => {
    editingLessonId.value = null;
    lessonUpdateForm.clearErrors();
};

const updateLesson = () => {
    if (!editingLessonId.value) {
        return;
    }

    lessonUpdateForm
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/admin/lessons/${editingLessonId.value}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                editingLessonId.value = null;
                resetLessonForm(lessonUpdateForm, makeLessonDefaults());
            },
        });
};

const moveLesson = (lessonId, direction) => {
    router.post(`/admin/lessons/${lessonId}/move`, { direction }, { preserveScroll: true });
};

const deleteLesson = async (lessonId) => {
    const confirmed = await confirmDestructive({
        title: isArabic.value ? 'هل تريد حذف هذا الدرس؟' : 'Delete this lesson?',
        text: isArabic.value
            ? 'سيتم حذف الدرس وكل المرفقات والصور التابعة له.'
            : 'The lesson and all related attachments and images will be deleted.',
        confirmButtonText: isArabic.value ? 'حذف الدرس' : 'Delete Lesson',
    });

    if (!confirmed) {
        return;
    }

    router.delete(`/admin/lessons/${lessonId}`, { preserveScroll: true });
};

const formatLabel = (value) => {
    if (value === 'video') {
        return isArabic.value ? 'مرئي' : 'Video';
    }

    if (value === 'text') {
        return isArabic.value ? 'كتابي' : 'Text';
    }

    return isArabic.value ? 'مختلط' : 'Mixed';
};

const courseStatusLabel = (status) => {
    if (status === 'published') {
        return t('admin.published');
    }

    if (status === 'archived') {
        return isArabic.value ? 'مؤرشف' : 'Archived';
    }

    return t('admin.draft');
};

const courseStatusClass = (status) => {
    if (status === 'published') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (status === 'archived') {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-slate-100 text-slate-700';
};
</script>
