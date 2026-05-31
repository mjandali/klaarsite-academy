<template>
    <Head :title="currentLesson.title" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <LearningShell
                    :course="course"
                    :enrollment="enrollment"
                    :completed-lesson-ids="completedLessonIds"
                    :current-lesson-id="currentLesson.id"
                    :course-overview-url="courseOverviewUrl"
                    :course-completed="courseCompleted"
                >
                    <article class="surface-card overflow-hidden">
                        <div class="border-b p-6 md:p-8">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                                    {{ formatLabel(currentLesson.type) }}
                                </span>
                                <span
                                    v-if="isCompleted"
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700"
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

                            <div class="mt-8 border-t pt-6">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <Link
                                            :href="courseOverviewUrl"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            {{ isArabic ? '← العودة إلى نظرة الكورس' : '← Back to Course Overview' }}
                                        </Link>
                                        <Link
                                            v-if="previousLesson"
                                            :href="`/dashboard/learn/${course.slug}/lessons/${previousLesson.id}`"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            {{ isArabic ? '→ الدرس السابق' : '← Previous Lesson' }}
                                        </Link>
                                        <Link
                                            v-if="nextLesson"
                                            :href="`/dashboard/learn/${course.slug}/lessons/${nextLesson.id}`"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            {{ isArabic ? 'الدرس التالي ←' : 'Next Lesson →' }}
                                        </Link>
                                        <Link
                                            v-if="exerciseQuestionsCount > 0"
                                            :href="`/dashboard/learn/${course.slug}/lessons/${currentLesson.id}/exercise`"
                                            class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 font-bold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            {{ isArabic ? `حل تمرين الدرس (${exerciseQuestionsCount})` : `Lesson exercise (${exerciseQuestionsCount})` }}
                                        </Link>
                                    </div>

                                    <form class="lg:ms-auto" @submit.prevent="complete">
                                        <button
                                            class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 font-bold text-white transition lg:w-auto"
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
    exerciseQuestionsCount: { type: Number, default: 0 },
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
