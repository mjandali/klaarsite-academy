<template>
    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)] xl:gap-8">
        <aside data-learning-sidebar="desktop" class="surface-card hidden h-fit p-5 xl:sticky xl:top-24 xl:block">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ isArabic ? 'رحلة التعلم' : 'Learning Path' }}</p>
                    <h2 class="mt-2 text-xl font-extrabold">{{ course.title }}</h2>
                </div>
                <Link
                    v-if="courseOverviewUrl && currentLessonId"
                    :href="courseOverviewUrl"
                    class="text-sm font-semibold text-blue-700 hover:underline"
                >
                    {{ isArabic ? 'نظرة الكورس' : 'Overview' }}
                </Link>
            </div>

            <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                <div class="mb-2 flex justify-between text-sm font-semibold text-slate-600">
                    <span>{{ t('student.progress') }}</span>
                    <span>{{ progressPercentage }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-3 rounded-full bg-blue-700" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <div
                v-if="courseCompleted"
                class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800"
            >
                {{ isArabic ? 'أكملت هذا الكورس بالفعل ويمكنك الآن مراجعة أي درس متى شئت.' : 'You completed this course already and can review any lesson whenever you like.' }}
            </div>

            <nav class="mt-5 max-h-[62vh] space-y-5 overflow-y-auto pe-1">
                <div v-for="section in course.sections" :key="section.id">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold text-slate-500">{{ section.title }}</h3>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500">
                            {{ section.lessons.length }}
                        </span>
                    </div>

                    <div v-if="section.lessons.length" class="space-y-2">
                        <Link
                            v-for="lesson in section.lessons"
                            :key="lesson.id"
                            :href="lessonUrl(lesson)"
                            class="block rounded-2xl border px-3 py-3 transition"
                            :class="lesson.id === currentLessonId ? 'border-blue-700 bg-blue-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-blue-50/50'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="isCompleted(lesson.id)"
                                            class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold"
                                            :class="lesson.id === currentLessonId ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'"
                                        >
                                            ✓
                                        </span>
                                        <p class="truncate font-semibold">{{ lesson.title }}</p>
                                    </div>
                                    <p class="mt-2 text-xs" :class="lesson.id === currentLessonId ? 'text-blue-100' : 'text-slate-500'">
                                        {{ lesson.duration_minutes || 0 }} {{ isArabic ? 'دقيقة' : 'min' }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full px-2 py-1 text-[11px] font-bold uppercase tracking-wide"
                                    :class="lesson.id === currentLessonId ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-700'"
                                >
                                    {{ formatLabel(lesson.type) }}
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-xs text-slate-500">
                        {{ isArabic ? 'لا توجد دروس منشورة في هذا القسم بعد.' : 'No published lessons in this section yet.' }}
                    </div>
                </div>
            </nav>
        </aside>

        <div class="min-w-0">
            <slot />
        </div>

        <button
            data-learning-toggle="mobile"
            type="button"
            class="fixed bottom-5 z-50 inline-flex items-center gap-2 rounded-full bg-slate-950 px-4 py-3 text-sm font-bold text-white shadow-lg xl:hidden"
            :class="isArabic ? 'left-5' : 'right-5'"
            @click="isDrawerOpen = true"
        >
            <span>☰</span>
            <span>{{ isArabic ? 'المنهج' : 'Curriculum' }}</span>
        </button>

        <Teleport to="body">
            <div v-if="isDrawerOpen" class="fixed inset-0 z-[70] xl:hidden">
                <button type="button" class="absolute inset-0 bg-slate-950/55" aria-label="Close curriculum drawer" @click="isDrawerOpen = false"></button>

                <aside
                    data-learning-sidebar="drawer"
                    class="absolute inset-y-0 w-full max-w-sm overflow-y-auto bg-white p-5 shadow-2xl"
                    :class="isArabic ? 'left-0' : 'right-0'"
                >
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ isArabic ? 'المنهج' : 'Curriculum' }}</p>
                            <h2 class="mt-2 text-xl font-extrabold">{{ course.title }}</h2>
                        </div>
                        <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-600" @click="isDrawerOpen = false">✕</button>
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                        <div class="mb-2 flex justify-between text-sm font-semibold text-slate-600">
                            <span>{{ t('student.progress') }}</span>
                            <span>{{ progressPercentage }}%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-3 rounded-full bg-blue-700" :style="{ width: `${progressPercentage}%` }"></div>
                        </div>
                    </div>

                    <nav class="mt-5 space-y-5">
                        <div v-for="section in course.sections" :key="`mobile-${section.id}`">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-bold text-slate-500">{{ section.title }}</h3>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500">
                                    {{ section.lessons.length }}
                                </span>
                            </div>

                            <div v-if="section.lessons.length" class="space-y-2">
                                <Link
                                    v-for="lesson in section.lessons"
                                    :key="`mobile-lesson-${lesson.id}`"
                                    :href="lessonUrl(lesson)"
                                    class="block rounded-2xl border px-3 py-3 transition"
                                    :class="lesson.id === currentLessonId ? 'border-blue-700 bg-blue-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-blue-50/50'"
                                    @click="isDrawerOpen = false"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    v-if="isCompleted(lesson.id)"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold"
                                                    :class="lesson.id === currentLessonId ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'"
                                                >
                                                    ✓
                                                </span>
                                                <p class="truncate font-semibold">{{ lesson.title }}</p>
                                            </div>
                                            <p class="mt-2 text-xs" :class="lesson.id === currentLessonId ? 'text-blue-100' : 'text-slate-500'">
                                                {{ lesson.duration_minutes || 0 }} {{ isArabic ? 'دقيقة' : 'min' }}
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full px-2 py-1 text-[11px] font-bold uppercase tracking-wide"
                                            :class="lesson.id === currentLessonId ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-700'"
                                        >
                                            {{ formatLabel(lesson.type) }}
                                        </span>
                                    </div>
                                </Link>
                            </div>

                            <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-xs text-slate-500">
                                {{ isArabic ? 'لا توجد دروس منشورة في هذا القسم بعد.' : 'No published lessons in this section yet.' }}
                            </div>
                        </div>
                    </nav>
                </aside>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({
    course: { type: Object, required: true },
    enrollment: { type: Object, required: true },
    completedLessonIds: { type: Array, default: () => [] },
    currentLessonId: { type: Number, default: null },
    courseOverviewUrl: { type: String, default: null },
    courseCompleted: { type: Boolean, default: false },
});

const page = usePage();
const { t } = useTranslations();
const isArabic = computed(() => page.props.locale.current === 'ar');
const isDrawerOpen = ref(false);
const progressPercentage = computed(() => Number(props.enrollment?.progress_percentage || 0));

const lessonUrl = (lesson) => `/dashboard/learn/${props.course.slug}/lessons/${lesson.id}`;

const isCompleted = (lessonId) => props.completedLessonIds.includes(lessonId);

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
