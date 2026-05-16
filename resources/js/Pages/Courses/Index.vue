<template>
    <PublicLayout>
        <Head :title="t('courses.title')" />

        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-3">{{ t('courses.title') }}</h1>
                <p class="page-lead mb-8 max-w-3xl sm:mb-10">{{ t('courses.subtitle') }}</p>

                <div v-if="courses.data.length" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <CourseCard v-for="course in courses.data" :key="course.id" :course="course" />
                </div>
                <div v-else class="surface-card p-10 text-center text-slate-500">
                    {{ t('courses.empty') }}
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import CourseCard from '@/Components/CourseCard.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ courses: Object });

const { t } = useTranslations();
</script>
