<template>
    <Head :title="t('admin.courses')" />
    <AdminLayout>
        <div class="py-10">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-4xl font-extrabold">{{ t('admin.courses') }}</h1>
                    <Link href="/admin/courses/create" class="bg-blue-700 text-white px-5 py-2 rounded-xl font-bold hover:bg-blue-800">
                        + {{ t('admin.new_course') }}
                    </Link>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead class="bg-slate-100 border-b">
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
                                    <span :class="course.is_published ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700'" class="px-2 py-1 rounded text-sm">
                                        {{ course.is_published ? t('admin.published') : t('admin.draft') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">{{ course.sections_count }} / {{ course.lessons_count }}</td>
                                <td class="px-5 py-4">{{ course.enrollments_count }}</td>
                                <td class="px-5 py-4 flex gap-3">
                                    <Link :href="`/admin/courses/${course.id}/edit`" class="text-blue-700 hover:underline">{{ t('admin.edit') }}</Link>
                                    <button @click="destroy(course.id)" class="text-red-600 hover:underline">{{ t('admin.delete') }}</button>
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
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ courses: Object });
const { t } = useTranslations();
const destroy = (id) => {
    if (confirm('Delete this course?')) router.delete(`/admin/courses/${id}`);
};
</script>
