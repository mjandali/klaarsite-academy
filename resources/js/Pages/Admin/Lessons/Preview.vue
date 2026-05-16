<template>
    <Head :title="currentLesson.title" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container grid gap-6 xl:grid-cols-4 xl:gap-8">
                <aside class="surface-card h-fit p-5 xl:sticky xl:top-24 xl:col-span-1">
                    <Link :href="builderUrl" class="font-semibold text-blue-700 hover:underline">
                        {{ isArabic ? 'العودة إلى بناء الكورس' : 'Back to course builder' }}
                    </Link>

                    <div class="mt-4 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-bold">{{ isArabic ? 'وضع المعاينة' : 'Preview Mode' }}</p>
                        <p class="mt-1 leading-6">
                            {{ isArabic ? 'هذه المعاينة متاحة للإدارة فقط، ويمكن أن تعرض دروساً مسودة غير مرئية للطلاب.' : 'This preview is only available to admins and can include draft lessons hidden from students.' }}
                        </p>
                    </div>

                    <h2 class="mt-5 text-xl font-extrabold">{{ course.title }}</h2>

                    <nav class="mt-5 max-h-[60vh] space-y-5 overflow-y-auto pr-1">
                        <div v-for="section in course.sections" :key="section.id">
                            <h3 class="mb-2 text-sm font-bold text-slate-500">{{ section.title }}</h3>
                            <div v-if="section.lessons.length" class="space-y-1">
                                <Link
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    :href="`/admin/lessons/${lesson.id}/preview`"
                                    class="block rounded-lg px-3 py-2 text-sm"
                                    :class="lesson.id === currentLesson.id ? 'bg-blue-700 text-white' : 'text-slate-700 hover:bg-slate-100'"
                                >
                                    <span class="font-semibold">{{ lesson.title }}</span>
                                    <span class="mt-1 block text-xs opacity-80">{{ lesson.status === 'published' ? t('admin.published') : t('admin.draft') }}</span>
                                </Link>
                            </div>
                            <div v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-3 text-xs text-slate-500">
                                {{ isArabic ? 'لا توجد دروس في هذا القسم بعد.' : 'No lessons in this section yet.' }}
                            </div>
                        </div>
                    </nav>
                </aside>

                <article class="surface-card overflow-hidden xl:col-span-3">
                    <div class="border-b p-6 md:p-8">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                                {{ formatLabel(currentLesson.type) }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="currentLesson.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                                {{ currentLesson.status === 'published' ? t('admin.published') : t('admin.draft') }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-extrabold md:text-4xl">{{ currentLesson.title }}</h1>
                        <div v-if="currentLesson.description" class="lesson-prose mt-4 text-sm text-slate-600" v-html="currentLesson.description"></div>
                    </div>

                    <div v-if="currentLesson.video_embed_url" class="aspect-video bg-black">
                        <iframe :src="currentLesson.video_embed_url" class="h-full w-full" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>

                    <div class="p-6 md:p-8">
                        <div v-if="currentLesson.content" class="lesson-prose text-slate-700" v-html="currentLesson.content"></div>
                        <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-7 text-slate-500">
                            {{ isArabic ? 'لا يوجد محتوى مكتوب لهذا الدرس حالياً.' : 'No written content is available for this lesson yet.' }}
                        </div>

                        <div v-if="currentLesson.attachments?.length" class="mt-8 border-t pt-6">
                            <h2 class="mb-4 text-xl font-extrabold">{{ isArabic ? 'المرفقات' : 'Attachments' }}</h2>
                            <div class="space-y-2">
                                <div
                                    v-for="attachment in currentLesson.attachments"
                                    :key="attachment.id"
                                    class="rounded-xl border border-slate-200 px-4 py-3"
                                >
                                    <p class="font-semibold text-slate-800">{{ attachment.file_name }}</p>
                                    <p class="text-xs text-slate-500">{{ formatFileSize(attachment.file_size) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3 border-t pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Link
                                    v-if="previousLesson"
                                    :href="`/admin/lessons/${previousLesson.id}/preview`"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    {{ isArabic ? 'معاينة الدرس السابق' : 'Preview Previous Lesson' }}
                                </Link>
                                <Link
                                    v-if="nextLesson"
                                    :href="`/admin/lessons/${nextLesson.id}/preview`"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    {{ isArabic ? 'معاينة الدرس التالي' : 'Preview Next Lesson' }}
                                </Link>
                            </div>

                            <Link :href="builderUrl" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-6 py-3 font-bold text-white transition hover:bg-blue-800 sm:ms-auto">
                                {{ isArabic ? 'العودة للتحرير' : 'Back to Editing' }}
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({
    course: { type: Object, required: true },
    currentLesson: { type: Object, required: true },
    previousLesson: { type: Object, default: null },
    nextLesson: { type: Object, default: null },
    builderUrl: { type: String, required: true },
});

const page = usePage();
const { t } = useTranslations();
const isArabic = computed(() => page.props.locale.current === 'ar');

const formatLabel = (value) => {
    if (value === 'video') {
        return isArabic.value ? 'مرئي' : 'Video';
    }

    if (value === 'text') {
        return isArabic.value ? 'كتابي' : 'Text';
    }

    return isArabic.value ? 'مختلط' : 'Mixed';
};

const formatFileSize = (bytes) => {
    if (!bytes) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB'];
    const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

    return `${(bytes / (1024 ** unitIndex)).toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
};
</script>
