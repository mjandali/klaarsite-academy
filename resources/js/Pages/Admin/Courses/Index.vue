<template>
    <Head :title="t('admin.courses')" />

    <AdminLayout>
        <div class="py-10">
            <div class="container mx-auto px-4">
                <div class="mb-8 flex items-center justify-between">
                    <h1 class="text-4xl font-extrabold">{{ t('admin.courses') }}</h1>
                    <Link href="/admin/courses/create" class="rounded-xl bg-blue-700 px-5 py-2 font-bold text-white hover:bg-blue-800">
                        + {{ t('admin.new_course') }}
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-2xl border bg-white shadow-sm">
                    <table class="w-full min-w-[760px]">
                        <thead class="border-b bg-slate-100">
                            <tr>
                                <th class="px-5 py-3 text-start">{{ t('admin.title') }}</th>
                                <th class="px-5 py-3 text-start">{{ t('admin.price') }}</th>
                                <th class="px-5 py-3 text-start">Format</th>
                                <th class="px-5 py-3 text-start">{{ t('admin.status') }}</th>
                                <th class="px-5 py-3 text-start">{{ t('admin.sections') }}</th>
                                <th class="px-5 py-3 text-start">{{ t('admin.students') }}</th>
                                <th class="px-5 py-3 text-start">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="course in courses.data" :key="course.id" class="border-b hover:bg-slate-50">
                                <td class="px-5 py-4 font-bold">{{ course.title }}</td>
                                <td class="px-5 py-4">{{ Number(course.price) <= 0 ? t('common.free') : `${course.price} ${course.currency}` }}</td>
                                <td class="px-5 py-4 capitalize">{{ course.course_format }}</td>
                                <td class="px-5 py-4">
                                    <span
                                        :class="course.status === 'published' ? 'bg-green-100 text-green-800' : course.status === 'archived' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700'"
                                        class="rounded px-2 py-1 text-sm"
                                    >
                                        {{ course.status === 'published' ? t('admin.published') : course.status === 'archived' ? 'Archived' : t('admin.draft') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">{{ course.sections_count }} / {{ course.lessons_count }}</td>
                                <td class="px-5 py-4">{{ course.enrollments_count }}</td>
                                <td class="flex gap-3 px-5 py-4">
                                    <Link :href="`/admin/courses/${course.id}/edit`" class="text-blue-700 hover:underline">{{ t('admin.edit') }}</Link>
                                    <button type="button" class="text-red-600 hover:underline" @click="destroy(course.id)">{{ t('admin.delete') }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!courses.data.length" class="p-8 text-center text-slate-500">{{ t('common.empty') }}</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ courses: Object });

const page = usePage();
const { t } = useTranslations();
const { confirmDestructive } = useConfirm();
const isArabic = computed(() => page.props.locale.current === 'ar');

const destroy = async (id) => {
    const confirmed = await confirmDestructive({
        title: isArabic.value ? 'هل تريد حذف هذا الكورس؟' : 'Delete this course?',
        text: isArabic.value
            ? 'سيتم حذف الكورس وأقسامه ودروسه وكل المحتوى المرتبط به.'
            : 'The course, its sections, lessons, and related content will be deleted.',
        confirmButtonText: isArabic.value ? 'حذف الكورس' : 'Delete Course',
    });

    if (!confirmed) {
        return;
    }

    router.delete(`/admin/courses/${id}`);
};
</script>
