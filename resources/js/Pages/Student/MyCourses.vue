<template>
    <Head :title="t('nav.my_courses')" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">{{ t('nav.my_courses') }}</h1>

                <div v-if="courses.data.length" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <div v-for="enrollment in courses.data" :key="enrollment.id" class="surface-card p-6">
                        <h2 class="mb-2 text-xl font-bold">{{ enrollment.course.title }}</h2>
                        <p class="mb-5 text-sm text-slate-600">{{ enrollment.course.subtitle }}</p>

                        <div class="mb-5">
                            <div class="mb-1 flex justify-between text-sm">
                                <span>{{ t('student.progress') }}</span>
                                <span>{{ enrollment.progress_percentage }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-blue-700" :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                            </div>
                        </div>

                        <Link
                            :href="enrollment.continue_url || `/dashboard/learn/${enrollment.course.slug}`"
                            class="block rounded-xl bg-blue-700 py-2 text-center font-bold text-white transition hover:bg-blue-800"
                        >
                            {{ t('student.continue') }}
                        </Link>
                    </div>
                </div>

                <CourseEnrollmentList v-else :enrollments="[]" />
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import CourseEnrollmentList from '@/Components/CourseEnrollmentList.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ courses: Object });

const { t } = useTranslations();
</script>
