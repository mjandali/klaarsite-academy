<template>
    <PublicLayout>
        <Head :title="course.title" />

        <section class="bg-slate-950 py-14 text-white">
            <div class="page-container grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="mb-3 font-semibold text-blue-300">Klaarsite Academy</p>
                    <h1 class="mb-5 text-4xl font-extrabold leading-tight md:text-5xl">{{ course.title }}</h1>
                    <p class="mb-6 text-base leading-8 text-slate-300 sm:text-lg">{{ course.subtitle }}</p>

                    <div class="mb-8 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full bg-white/10 px-3 py-1">{{ t('courses.level') }}: {{ course.level }}</span>
                        <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-emerald-100">{{ formatLabel }}</span>
                        <span class="rounded-full bg-white/10 px-3 py-1">{{ course.lessons_count || 0 }} {{ t('courses.lessons') }}</span>
                        <span class="rounded-full bg-white/10 px-3 py-1">{{ course.duration_hours || 0 }}h</span>
                    </div>

                    <form v-if="$page.props.auth.user && !isEnrolled" class="w-full sm:w-auto" @submit.prevent="checkout">
                        <button
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-bold text-white transition hover:bg-blue-700 sm:w-auto"
                            :disabled="form.processing"
                        >
                            {{ Number(course.price) <= 0 ? t('courses.enroll_free') : t('courses.buy') }} &middot; {{ priceLabel }}
                        </button>
                    </form>
                    <Link
                        v-else-if="isEnrolled"
                        :href="`/dashboard/learn/${course.slug}`"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-green-600 px-8 py-3 font-bold text-white transition hover:bg-green-700 sm:w-auto"
                    >
                        {{ t('courses.continue') }}
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-bold text-white transition hover:bg-blue-700 sm:w-auto"
                    >
                        {{ t('nav.login') }}
                    </Link>
                </div>

                <div class="flex min-h-[280px] items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 to-slate-800 shadow-2xl">
                    <img v-if="thumbnail" :src="thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                    <span v-else class="text-6xl font-extrabold text-white/80">KA</span>
                </div>
            </div>
        </section>

        <section class="page-shell">
            <div class="page-container grid gap-8 lg:grid-cols-3 lg:gap-10">
                <article class="surface-card p-6 sm:p-8 lg:col-span-2">
                    <h2 class="mb-5 text-2xl font-extrabold">{{ t('courses.included') }}</h2>
                    <div class="prose-content text-slate-700" v-html="course.description || ''"></div>
                </article>

                <aside class="surface-card h-fit p-6">
                    <h2 class="mb-5 text-xl font-extrabold">{{ t('courses.curriculum') }}</h2>
                    <div class="space-y-5">
                        <div v-for="section in course.sections" :key="section.id">
                            <h3 class="mb-2 font-bold">{{ section.title }}</h3>
                            <ul class="space-y-2 text-sm text-slate-600">
                                <li
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    class="flex flex-col gap-1 border-b border-slate-200 pb-2 last:border-b-0 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <span>{{ lesson.title }}</span>
                                    <div class="flex items-center gap-2 text-slate-500">
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] uppercase tracking-wide text-slate-600">{{ lesson.type }}</span>
                                        <span>{{ lesson.duration_minutes || 0 }}m</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({ course: Object, isEnrolled: Boolean });

const { t } = useTranslations();
const form = useForm({});
const thumbnail = computed(() =>
    props.course.thumbnail_url?.startsWith('http')
        ? props.course.thumbnail_url
        : props.course.thumbnail_url
          ? `/storage/${props.course.thumbnail_url}`
          : null
);
const priceLabel = computed(() =>
    Number(props.course.price) <= 0 ? t('courses.price_free') : `${props.course.price} ${props.course.currency}`
);
const formatLabel = computed(() => {
    const format = props.course.course_format || 'mixed';

    return format.charAt(0).toUpperCase() + format.slice(1);
});
const checkout = () => form.post(`/courses/${props.course.slug}/checkout`);
</script>
