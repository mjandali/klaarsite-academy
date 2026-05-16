<template>
    <Head :title="course.title" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <LearningShell
                    :course="course"
                    :enrollment="enrollment"
                    :completed-lesson-ids="completedLessonIds"
                    :course-completed="courseCompleted"
                >
                    <div class="space-y-6">
                        <article class="surface-card overflow-hidden">
                            <div class="border-b p-6 md:p-8">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                                                {{ formatLabel(course.course_format) }}
                                            </span>
                                            <span
                                                v-if="courseCompleted"
                                                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700"
                                            >
                                                {{ isArabic ? 'تم الإكمال' : 'Completed' }}
                                            </span>
                                        </div>

                                        <h1 class="mt-4 text-3xl font-extrabold md:text-4xl">{{ course.title }}</h1>
                                        <p v-if="course.subtitle" class="mt-3 text-base leading-8 text-slate-600">{{ course.subtitle }}</p>
                                    </div>

                                    <Link
                                        v-if="resumeUrl"
                                        :href="resumeUrl"
                                        class="inline-flex items-center justify-center rounded-2xl bg-blue-700 px-5 py-3 text-center font-bold text-white transition hover:bg-blue-800"
                                    >
                                        {{ primaryActionLabel }}
                                    </Link>
                                </div>
                            </div>

                            <div class="grid gap-4 p-6 sm:grid-cols-3 md:p-8">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-sm text-slate-500">{{ t('courses.sections') }}</p>
                                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ course.sections.length }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-sm text-slate-500">{{ t('courses.lessons') }}</p>
                                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ publishedLessonsCount }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-sm text-slate-500">{{ t('student.progress') }}</p>
                                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ enrollment.progress_percentage }}%</p>
                                </div>
                            </div>
                        </article>

                        <article v-if="course.description" class="surface-card p-6 md:p-8">
                            <h2 class="mb-4 text-2xl font-extrabold">{{ isArabic ? 'عن هذا الكورس' : 'About This Course' }}</h2>
                            <div class="lesson-prose text-slate-700" v-html="course.description"></div>
                        </article>

                        <div v-if="!course.sections.length" class="surface-card border-dashed p-8 text-center text-slate-500">
                            <h2 class="text-xl font-extrabold text-slate-800">{{ isArabic ? 'لا توجد أقسام في هذا الكورس بعد' : 'This course has no sections yet' }}</h2>
                            <p class="mt-2 text-sm leading-7">
                                {{ isArabic ? 'عندما يضيف المشرف أقسامًا ودروسًا ستظهر هنا لتبدأ التعلّم.' : 'Sections and lessons will appear here as soon as the admin publishes them.' }}
                            </p>
                        </div>

                        <div v-else-if="!publishedLessonsCount" class="surface-card border-dashed p-8 text-center text-slate-500">
                            <h2 class="text-xl font-extrabold text-slate-800">{{ isArabic ? 'لا توجد دروس منشورة بعد' : 'No published lessons yet' }}</h2>
                            <p class="mt-2 text-sm leading-7">
                                {{ isArabic ? 'الكورس قيد الإعداد حالياً. ستتمكن من متابعة التعلم بمجرد نشر الدروس.' : 'The course is still being prepared. You will be able to continue once lessons are published.' }}
                            </p>
                        </div>

                        <div class="space-y-5">
                            <article v-for="section in course.sections" :key="section.id" class="surface-card p-5 shadow-sm">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-xl font-extrabold">{{ section.title }}</h2>
                                        <p v-if="section.description" class="mt-1 text-sm leading-6 text-slate-500">{{ section.description }}</p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ section.lessons.length }} {{ t('courses.lessons') }}
                                    </span>
                                </div>

                                <div v-if="section.lessons.length" class="mt-5 space-y-3">
                                    <Link
                                        v-for="lesson in section.lessons"
                                        :key="lesson.id"
                                        :href="`/dashboard/learn/${course.slug}/lessons/${lesson.id}`"
                                        class="flex flex-col gap-3 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-blue-200 hover:bg-blue-50/40 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span v-if="completedLessonIds.includes(lesson.id)" class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700">
                                                    {{ t('student.completed_badge') }}
                                                </span>
                                                <h3 class="truncate font-bold text-slate-900">{{ lesson.title }}</h3>
                                            </div>
                                            <p v-if="lesson.description" class="mt-1 truncate text-sm text-slate-500">{{ plainText(lesson.description) }}</p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 text-xs">
                                            <span class="rounded-full bg-blue-50 px-2 py-1 font-semibold text-blue-700">{{ formatLabel(lesson.type) }}</span>
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-600">{{ lesson.duration_minutes || 0 }} {{ isArabic ? 'دقيقة' : 'min' }}</span>
                                        </div>
                                    </Link>
                                </div>

                                <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm text-slate-500">
                                    {{ isArabic ? 'لا توجد دروس منشورة داخل هذا القسم بعد.' : 'No published lessons are available in this section yet.' }}
                                </div>
                            </article>
                        </div>
                    </div>
                </LearningShell>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import LearningShell from '@/Components/LearningShell.vue';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({
    course: Object,
    enrollment: Object,
    completedLessonIds: Array,
    resumeUrl: String,
    courseCompleted: Boolean,
    publishedLessonsCount: Number,
});

const page = usePage();
const { t } = useTranslations();
const isArabic = computed(() => page.props.locale.current === 'ar');

const primaryActionLabel = computed(() => {
    if (props.courseCompleted) {
        return isArabic.value ? 'مراجعة الدروس' : 'Review Lessons';
    }

    if (props.completedLessonIds.length) {
        return t('student.continue');
    }

    return isArabic.value ? 'ابدأ التعلّم' : 'Start Learning';
});

const formatLabel = (value) => {
    if (value === 'video') {
        return isArabic.value ? 'مرئي' : 'Video';
    }

    if (value === 'text') {
        return isArabic.value ? 'كتابي' : 'Text';
    }

    return isArabic.value ? 'مختلط' : 'Mixed';
};

const plainText = (value) => value.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
</script>
