<template>
    <Head :title="course.title" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container space-y-8">
                <div class="surface-card overflow-hidden">
                    <div class="bg-slate-950 px-6 py-8 text-white sm:px-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="max-w-3xl">
                                <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">Klaarsite Academy</p>
                                <h1 class="text-3xl font-extrabold sm:text-4xl">{{ course.title }}</h1>
                                <p v-if="course.subtitle" class="mt-3 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">{{ course.subtitle }}</p>

                                <div class="mt-5 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-white/10 px-3 py-1">{{ formatLabel(course.course_format) }}</span>
                                    <span class="rounded-full bg-white/10 px-3 py-1">{{ publishedLessonsCount }} {{ t('courses.lessons') }}</span>
                                    <span class="rounded-full bg-white/10 px-3 py-1">{{ enrollment.progress_percentage }}% {{ t('student.progress') }}</span>
                                </div>
                            </div>

                            <div class="flex w-full flex-col gap-3 sm:w-auto">
                                <Link
                                    v-if="resumeUrl"
                                    :href="resumeUrl"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700"
                                >
                                    {{ primaryActionLabel }}
                                </Link>
                                <Link href="/dashboard" class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
                                    {{ t('student.back_to_dashboard') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 xl:grid-cols-[minmax(0,280px)_minmax(0,1fr)]">
                        <aside class="space-y-5">
                            <div class="rounded-2xl bg-slate-50 p-5">
                                <div class="mb-2 flex justify-between text-sm font-semibold text-slate-600">
                                    <span>{{ t('student.progress') }}</span>
                                    <span>{{ enrollment.progress_percentage }}%</span>
                                </div>
                                <div class="h-3 rounded-full bg-slate-200">
                                    <div class="h-3 rounded-full bg-blue-700" :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                                </div>
                            </div>

                            <div
                                v-if="courseCompleted"
                                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm leading-7 text-emerald-800"
                            >
                                <h2 class="font-extrabold">{{ isArabic ? 'أكملت هذا الكورس' : 'You completed this course' }}</h2>
                                <p class="mt-2">
                                    {{ isArabic ? 'أحسنت. يمكنك مراجعة أي درس من المنهج في أي وقت.' : 'Nice work. You can review any lesson from the curriculum whenever you want.' }}
                                </p>
                            </div>

                            <div
                                v-else-if="!resumeUrl"
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-7 text-slate-600"
                            >
                                {{ isArabic ? 'سيظهر زر المتابعة هنا بعد إضافة دروس منشورة إلى هذا الكورس.' : 'A continue button will appear here once the course has published lessons.' }}
                            </div>
                        </aside>

                        <div class="space-y-6">
                            <div v-if="!course.sections.length" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
                                <h2 class="text-xl font-extrabold text-slate-800">{{ isArabic ? 'لا توجد أقسام في هذا الكورس بعد' : 'This course has no sections yet' }}</h2>
                                <p class="mt-2 text-sm leading-7">
                                    {{ isArabic ? 'عندما يضيف المشرف أقساماً ودروساً ستظهر هنا لتبدأ التعلّم.' : 'When the admin adds sections and lessons, they will appear here so you can begin learning.' }}
                                </p>
                            </div>

                            <div
                                v-else-if="!publishedLessonsCount"
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500"
                            >
                                <h2 class="text-xl font-extrabold text-slate-800">{{ isArabic ? 'لا توجد دروس منشورة بعد' : 'No published lessons yet' }}</h2>
                                <p class="mt-2 text-sm leading-7">
                                    {{ isArabic ? 'المحتوى قيد الإعداد حالياً. ستتمكن من متابعة الكورس بمجرد نشر الدروس.' : 'The course is still being prepared. You will be able to continue once lessons are published.' }}
                                </p>
                            </div>

                            <article v-for="section in course.sections" :key="section.id" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
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
                </div>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
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
