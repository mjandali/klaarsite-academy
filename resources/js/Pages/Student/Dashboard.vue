<template>
    <Head :title="t('student.dashboard')" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-2">{{ t('student.dashboard') }}</h1>
                <p class="page-lead mb-8">{{ t('student.welcome') }}, {{ $page.props.auth.user.name }}</p>

                <div class="mb-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <StatCard :label="t('student.total_courses')" :value="stats.total" />
                    <StatCard :label="t('student.in_progress')" :value="stats.in_progress" />
                    <StatCard :label="t('student.completed')" :value="stats.completed" />
                </div>

                <CourseEnrollmentList :enrollments="enrollments.data" />
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import CourseEnrollmentList from '@/Components/CourseEnrollmentList.vue';
import StatCard from '@/Components/StatCard.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ enrollments: Object, stats: Object });

const { t } = useTranslations();
</script>
