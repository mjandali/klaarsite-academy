<template>
    <PublicLayout>
        <Head :title="t('nav.home')" />

        <section class="bg-gradient-to-br from-blue-800 via-blue-700 to-slate-900 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="mb-5 inline-flex rounded-full bg-white/10 px-4 py-1 text-blue-100">Klaarsite Academy</p>
                <h1 class="mb-6 text-4xl font-extrabold leading-tight sm:text-5xl md:text-6xl">{{ t('home.title') }}</h1>
                <p class="mx-auto mb-8 max-w-3xl text-base leading-8 text-blue-100 sm:text-lg md:text-xl">{{ t('home.subtitle') }}</p>
                <Link
                    href="/courses"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-3 font-bold text-blue-800 shadow-lg transition hover:bg-blue-50 sm:w-auto"
                >
                    {{ t('home.cta') }}
                </Link>
            </div>
        </section>

        <section class="page-shell">
            <div class="page-container">
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <h2 class="text-3xl font-extrabold">{{ t('home.featured') }}</h2>
                    <Link href="/courses" class="font-semibold text-blue-700 hover:underline">{{ t('nav.courses') }}</Link>
                </div>

                <div v-if="featuredCourses.length" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <CourseCard v-for="course in featuredCourses" :key="course.id" :course="course" />
                </div>
                <div v-else class="surface-card p-10 text-center text-slate-500">
                    {{ t('home.empty') }}
                </div>
            </div>
        </section>

        <section class="bg-blue-700 py-16 text-white">
            <div class="page-container text-center">
                <h2 class="mb-6 text-3xl font-extrabold">{{ t('home.ready') }}</h2>
                <Link
                    href="/register"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-3 font-bold text-blue-800 transition hover:bg-blue-50 sm:w-auto"
                >
                    {{ t('home.signup') }}
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import CourseCard from '@/Components/CourseCard.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ featuredCourses: { type: Array, default: () => [] } });

const { t } = useTranslations();
</script>
