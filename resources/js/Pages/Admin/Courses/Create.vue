<template>
    <Head :title="t('admin.new_course')" />
    <AdminLayout>
        <div class="py-10">
            <div class="container mx-auto px-4 max-w-4xl">
                <Link href="/admin/courses" class="text-blue-700 hover:underline">← {{ t('common.back') }}</Link>
                <h1 class="text-4xl font-extrabold my-6">{{ t('admin.new_course') }}</h1>
                <CourseForm :form="form" submit-label="Create" @submit="submit" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourseForm from '@/Components/CourseForm.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
const form = useForm({
    title: '',
    subtitle: '',
    description: '',
    price: 0,
    currency: 'USD',
    level: 'beginner',
    duration_hours: '',
    meta_description: '',
    course_format: 'mixed',
    status: 'draft',
    thumbnail: null,
});
const submit = () => form.post('/admin/courses', { forceFormData: true });
</script>
