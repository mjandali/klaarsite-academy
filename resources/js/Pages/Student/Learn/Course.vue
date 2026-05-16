<template>
    <Head :title="course.title" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <div class="surface-card p-6 text-center shadow-sm sm:p-10">
                    <h1 class="mb-4 text-2xl font-extrabold sm:text-3xl">{{ course.title }}</h1>
                    <p class="mx-auto mb-6 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                        {{
                            $page.props.locale.current === 'ar'
                                ? 'لا توجد دروس منشورة في هذا الكورس بعد.'
                                : 'No published lessons are available in this course yet.'
                        }}
                    </p>
                    <Link href="/dashboard" class="font-semibold text-blue-700 hover:underline">{{ t('student.back_to_dashboard') }}</Link>
                </div>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ course: Object, enrollment: Object, currentLesson: Object, completedLessonIds: Array });

const { t } = useTranslations();
</script>
