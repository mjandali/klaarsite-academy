<template>
    <Head :title="currentLesson.title" />

    <StudentLayout>
        <section class="py-8 sm:py-10">
            <div class="page-container grid gap-6 xl:grid-cols-4 xl:gap-8">
                <aside class="surface-card h-fit p-5 xl:sticky xl:top-24 xl:col-span-1">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-xl font-extrabold">{{ course.title }}</h2>
                        <Link :href="courseOverviewUrl" class="text-sm font-semibold text-blue-700 hover:underline">
                            {{ isArabic ? 'نظرة الكورس' : 'Course Overview' }}
                        </Link>
                    </div>

                    <div class="mt-5">
                        <div class="mb-1 flex justify-between text-sm">
                            <span>{{ t('student.progress') }}</span>
                            <span>{{ enrollment.progress_percentage }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200">
                            <div class="h-2 rounded-full bg-blue-700" :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                        </div>
                    </div>

                    <div
                        v-if="courseCompleted"
                        class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-800"
                    >
                        {{ isArabic ? 'أكملت الكورس بالفعل ويمكنك الآن مراجعة جميع الدروس.' : 'You already completed this course. Feel free to review any lesson.' }}
                    </div>

                    <nav class="mt-5 max-h-[60vh] space-y-5 overflow-y-auto pr-1">
                        <div v-for="section in course.sections" :key="section.id">
                            <h3 class="mb-2 text-sm font-bold text-slate-500">{{ section.title }}</h3>
                            <div class="space-y-1">
                                <Link
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    :href="`/dashboard/learn/${course.slug}/lessons/${lesson.id}`"
                                    class="block rounded-lg px-3 py-2 text-sm"
                                    :class="lesson.id === currentLesson.id ? 'bg-blue-700 text-white' : 'text-slate-700 hover:bg-slate-100'"
                                >
                                    <span v-if="completedLessonIds.includes(lesson.id)" class="me-1" aria-hidden="true">&#10003;</span>
                                    {{ lesson.title }}
                                </Link>
                            </div>
                        </div>
                    </nav>
                </aside>

                <article class="surface-card overflow-hidden xl:col-span-3">
                    <div class="border-b p-6 md:p-8">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                                {{ formatLabel(currentLesson.type) }}
                            </span>
                            <span
                                v-if="isCompleted"
                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-green-700"
                            >
                                {{ t('student.completed_badge') }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-extrabold md:text-4xl">{{ currentLesson.title }}</h1>
                        <div v-if="currentLesson.description" class="lesson-prose mt-4 text-sm text-slate-600" v-html="currentLesson.description"></div>
                    </div>

                    <div v-if="currentLesson.video_embed_url" class="aspect-video bg-black">
                        <iframe :src="currentLesson.video_embed_url" class="h-full w-full" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>

                    <div class="p-6 md:p-8">
                        <div v-if="currentLesson.content" class="lesson-prose text-slate-700" v-html="currentLesson.content"></div>
                        <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-7 text-slate-500">
                            {{ isArabic ? 'لا يوجد محتوى مكتوب لهذا الدرس بعد.' : 'No written content is available for this lesson yet.' }}
                        </div>

                        <div v-if="currentLesson.attachments?.length" class="mt-8 border-t pt-6">
                            <h2 class="mb-4 text-xl font-extrabold">{{ t('student.attachments') }}</h2>
                            <div class="space-y-2">
                                <a
                                    v-for="attachment in currentLesson.attachments"
                                    :key="attachment.id"
                                    :href="`/dashboard/attachments/${attachment.id}/download`"
                                    class="block rounded-xl border border-slate-200 px-4 py-3 font-semibold text-blue-700 transition hover:bg-slate-50"
                                >
                                    {{ attachment.file_name }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3 border-t pt-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <Link
                                        :href="courseOverviewUrl"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        {{ isArabic ? 'العودة إلى نظرة الكورس' : 'Back to Course Overview' }}
                                    </Link>
                                    <Link
                                        v-if="previousLesson"
                                        :href="`/dashboard/learn/${course.slug}/lessons/${previousLesson.id}`"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        {{ isArabic ? 'الدرس السابق' : 'Previous Lesson' }}
                                    </Link>
                                    <Link
                                        v-if="nextLesson"
                                        :href="`/dashboard/learn/${course.slug}/lessons/${nextLesson.id}`"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        {{ isArabic ? 'الدرس التالي' : 'Next Lesson' }}
                                    </Link>
                                </div>

                                <form class="sm:ms-auto" @submit.prevent="complete">
                                    <button
                                        class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 font-bold text-white transition sm:w-auto"
                                        :class="isCompleted ? 'cursor-not-allowed bg-slate-400' : 'bg-green-600 hover:bg-green-700'"
                                        :disabled="form.processing || isCompleted"
                                    >
                                        {{ isCompleted ? t('student.completed_badge') : t('student.mark_complete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({
    course: Object,
    enrollment: Object,
    currentLesson: Object,
    completedLessonIds: Array,
    previousLesson: Object,
    nextLesson: Object,
    isCompleted: Boolean,
    courseOverviewUrl: String,
    courseCompleted: Boolean,
});

const page = usePage();
const { t } = useTranslations();
const isArabic = computed(() => page.props.locale.current === 'ar');
const form = useForm({});

const complete = () => form.post(`/dashboard/lessons/${props.currentLesson.id}/complete`, { preserveScroll: true });

const formatLabel = (value) => {
    if (value === 'video') {
        return isArabic.value ? 'مرئي' : 'Video';
    }

    if (value === 'text') {
        return isArabic.value ? 'كتابي' : 'Text';
    }

    return isArabic.value ? 'مختلط' : 'Mixed';
};
</script>
