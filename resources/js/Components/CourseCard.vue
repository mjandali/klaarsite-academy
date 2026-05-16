<template>
    <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
        <div class="relative h-40 overflow-hidden bg-gradient-to-br from-blue-600 to-slate-900 sm:h-44">
            <img v-if="thumbnail" :src="thumbnail" class="h-full w-full object-cover" :alt="course.title" />
            <div v-else class="absolute inset-0 flex items-center justify-center text-2xl font-extrabold text-white">KA</div>
        </div>

        <div class="flex flex-1 flex-col p-5 sm:p-6">
            <div class="mb-3 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-blue-50 px-2 py-1 text-blue-700">{{ course.level }}</span>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700">{{ formatLabel }}</span>
                <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-700">{{ course.lessons_count || 0 }} {{ t('courses.lessons') }}</span>
            </div>

            <h3 class="mb-2 text-lg font-extrabold leading-7 sm:text-xl">{{ course.title }}</h3>
            <p class="mb-5 flex-1 text-sm leading-6 text-slate-600">{{ course.subtitle }}</p>

            <div class="mt-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-lg font-extrabold text-blue-700">{{ priceLabel }}</span>
                <Link
                    :href="`/courses/${course.slug}`"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-800 sm:w-auto"
                >
                    {{ t('courses.details') }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({ course: Object });
const { t } = useTranslations();

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
</script>
