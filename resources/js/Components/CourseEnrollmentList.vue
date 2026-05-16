<template>
    <div class="surface-card p-5 sm:p-6">
        <h2 class="mb-6 text-2xl font-extrabold">{{ t('nav.my_courses') }}</h2>

        <div v-if="enrollments.length" class="space-y-4">
            <div v-for="enrollment in enrollments" :key="enrollment.id" class="rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold">{{ enrollment.course.title }}</h3>
                        <p class="text-sm text-slate-500">{{ enrollment.course.lessons_count || 0 }} {{ t('courses.lessons') }}</p>
                    </div>
                    <Link
                        :href="enrollment.continue_url || `/dashboard/learn/${enrollment.course.slug}`"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-4 py-2 font-bold text-white transition hover:bg-blue-800 sm:w-auto"
                    >
                        {{ t('student.continue') }}
                    </Link>
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex justify-between text-sm text-slate-600">
                        <span>{{ t('student.progress') }}</span>
                        <span>{{ enrollment.progress_percentage }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-blue-700" :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="py-10 text-center text-slate-500">
            <p class="mb-4">{{ t('student.no_courses') }}</p>
            <Link href="/courses" class="font-semibold text-blue-700 hover:underline">{{ t('student.browse_courses') }}</Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ enrollments: { type: Array, default: () => [] } });

const { t } = useTranslations();
</script>
