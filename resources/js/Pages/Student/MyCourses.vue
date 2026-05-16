<template>
    <Head :title="t('nav.my_courses')" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">{{ t('nav.my_courses') }}</h1>
                <CourseEnrollmentList :enrollments="courses.data" />
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import CourseEnrollmentList from '@/Components/CourseEnrollmentList.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ courses: Object });

const { t } = useTranslations();
</script>
