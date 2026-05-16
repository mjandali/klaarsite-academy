<template>
    <Head :title="course.title" />
    <AdminLayout>
        <div class="py-10">
            <div class="container mx-auto px-4 max-w-6xl">
                <div class="flex justify-between items-start gap-4 mb-6">
                    <div>
                        <Link href="/admin/courses" class="text-blue-700 hover:underline">← {{ t('common.back') }}</Link>
                        <h1 class="text-4xl font-extrabold mt-4">{{ t('admin.edit_course') }}</h1>
                    </div>
                    <Link v-if="course.is_published" :href="`/courses/${course.slug}`" class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold">{{ t('nav.view_site') }}</Link>
                </div>

                <CourseForm :form="form" @submit="submitCourse" />

                <div class="grid lg:grid-cols-2 gap-8 mt-8">
                    <section class="bg-white border rounded-2xl shadow-sm p-6">
                        <h2 class="text-2xl font-extrabold mb-5">{{ t('admin.add_section') }}</h2>
                        <form @submit.prevent="addSection" class="space-y-4">
                            <input v-model="sectionForm.title" class="w-full rounded-lg border-slate-300" placeholder="Section title" />
                            <textarea v-model="sectionForm.description" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Description"></textarea>
                            <input v-model="sectionForm.order" type="number" min="0" class="w-full rounded-lg border-slate-300" placeholder="Order" />
                            <button class="bg-blue-700 text-white px-5 py-2 rounded-xl font-bold" :disabled="sectionForm.processing">{{ t('admin.add_section') }}</button>
                        </form>
                    </section>

                    <section class="bg-white border rounded-2xl shadow-sm p-6">
                        <h2 class="text-2xl font-extrabold mb-5">{{ t('admin.add_lesson') }}</h2>
                        <form @submit.prevent="addLesson" class="space-y-4">
                            <select v-model="lessonForm.course_section_id" class="w-full rounded-lg border-slate-300">
                                <option disabled value="">Choose section</option>
                                <option v-for="section in course.sections" :key="section.id" :value="section.id">{{ section.title }}</option>
                            </select>
                            <input v-model="lessonForm.title" class="w-full rounded-lg border-slate-300" placeholder="Lesson title" />
                            <select v-model="lessonForm.type" class="w-full rounded-lg border-slate-300">
                                <option value="text">text</option><option value="video">video</option><option value="mixed">mixed</option>
                            </select>
                            <textarea v-model="lessonForm.description" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Short lesson description"></textarea>
                            <input v-model="lessonForm.video_url" class="w-full rounded-lg border-slate-300" placeholder="Video URL" />
                            <p class="text-xs text-slate-500">Only YouTube and Vimeo links are accepted. Unsafe HTML will be removed automatically.</p>
                            <textarea v-model="lessonForm.content" rows="5" class="w-full rounded-lg border-slate-300 font-mono text-sm" placeholder="Lesson content: safe HTML and code blocks are supported"></textarea>
                            <div class="grid md:grid-cols-2 gap-4">
                                <input v-model="lessonForm.duration_minutes" type="number" min="0" class="w-full rounded-lg border-slate-300" placeholder="Duration minutes" />
                                <input v-model="lessonForm.order" type="number" min="0" class="w-full rounded-lg border-slate-300" placeholder="Order" />
                            </div>
                            <input type="file" multiple accept=".pdf,.zip,.txt,.md,.doc,.docx,.xlsx,.png,.jpg,.jpeg,.webp" @input="lessonForm.attachments = [...$event.target.files]" class="w-full border rounded-lg p-2" />
                            <p class="text-xs text-slate-500">Allowed attachments: pdf, zip, txt, md, doc, docx, xlsx, png, jpg, jpeg, webp.</p>
                            <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="lessonForm.is_published" /> {{ t('admin.published') }}</label>
                            <button class="bg-blue-700 text-white px-5 py-2 rounded-xl font-bold" :disabled="lessonForm.processing">{{ t('admin.add_lesson') }}</button>
                        </form>
                    </section>
                </div>

                <section class="mt-8 bg-white border rounded-2xl shadow-sm p-6">
                    <h2 class="text-2xl font-extrabold mb-6">{{ t('courses.curriculum') }}</h2>
                    <div v-if="course.sections.length" class="space-y-8">
                        <div v-for="section in course.sections" :key="section.id" class="border rounded-2xl p-5">
                            <div class="flex justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-xl font-bold">{{ section.title }}</h3>
                                    <p class="text-slate-500 text-sm">{{ section.lessons.length }} {{ t('admin.lessons') }}</p>
                                </div>
                                <button @click="deleteSection(section.id)" class="text-red-600 hover:underline">{{ t('admin.delete') }}</button>
                            </div>

                            <details class="mb-5">
                                <summary class="cursor-pointer text-blue-700 font-semibold">{{ t('admin.edit') }} {{ t('admin.sections') }}</summary>
                                <form @submit.prevent="updateSection(section)" class="grid md:grid-cols-3 gap-3 mt-4">
                                    <input v-model="section.title" class="rounded-lg border-slate-300" />
                                    <input v-model="section.order" type="number" min="0" class="rounded-lg border-slate-300" />
                                    <button class="bg-slate-900 text-white rounded-lg font-bold">{{ t('admin.update') }}</button>
                                    <textarea v-model="section.description" rows="2" class="md:col-span-3 rounded-lg border-slate-300"></textarea>
                                </form>
                            </details>

                            <div class="space-y-3">
                                <details v-for="lesson in section.lessons" :key="lesson.id" class="border rounded-xl p-4">
                                    <summary class="cursor-pointer flex justify-between gap-3">
                                        <span class="font-semibold">{{ lesson.order }}. {{ lesson.title }}</span>
                                        <span :class="lesson.is_published ? 'text-green-700' : 'text-slate-500'">{{ lesson.is_published ? t('admin.published') : t('admin.draft') }}</span>
                                    </summary>
                                    <form @submit.prevent="updateLesson(lesson)" class="grid md:grid-cols-2 gap-3 mt-4">
                                        <select v-model="lesson.course_section_id" class="rounded-lg border-slate-300">
                                            <option v-for="target in course.sections" :key="target.id" :value="target.id">{{ target.title }}</option>
                                        </select>
                                        <input v-model="lesson.title" class="rounded-lg border-slate-300" />
                                        <select v-model="lesson.type" class="rounded-lg border-slate-300"><option>text</option><option>video</option><option>mixed</option></select>
                                        <textarea v-model="lesson.description" rows="3" class="md:col-span-2 rounded-lg border-slate-300" placeholder="Short lesson description"></textarea>
                                        <input v-model="lesson.video_url" class="rounded-lg border-slate-300" placeholder="Video URL" />
                                        <input v-model="lesson.duration_minutes" type="number" min="0" class="rounded-lg border-slate-300" />
                                        <input v-model="lesson.order" type="number" min="0" class="rounded-lg border-slate-300" />
                                        <p class="md:col-span-2 text-xs text-slate-500">Only YouTube and Vimeo links are accepted. Unsafe HTML will be removed automatically.</p>
                                        <textarea v-model="lesson.content" rows="6" class="md:col-span-2 rounded-lg border-slate-300 font-mono text-sm"></textarea>
                                        <input type="file" multiple accept=".pdf,.zip,.txt,.md,.doc,.docx,.xlsx,.png,.jpg,.jpeg,.webp" @input="lesson.new_attachments = [...$event.target.files]" class="md:col-span-2 border rounded-lg p-2" />
                                        <div v-if="lesson.attachments?.length" class="md:col-span-2 bg-slate-50 rounded-lg p-3 space-y-2">
                                            <label v-for="attachment in lesson.attachments" :key="attachment.id" class="flex items-center gap-2 text-sm">
                                                <input type="checkbox" :value="attachment.id" v-model="lesson.delete_attachments" />
                                                <span>{{ attachment.file_name }}</span>
                                            </label>
                                            <p class="text-xs text-slate-500">Checked attachments will be deleted when you update this lesson.</p>
                                        </div>
                                        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="lesson.is_published" /> {{ t('admin.published') }}</label>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="deleteLesson(lesson.id)" class="text-red-600 hover:underline">{{ t('admin.delete') }}</button>
                                            <button class="bg-blue-700 text-white px-5 py-2 rounded-xl font-bold">{{ t('admin.update') }}</button>
                                        </div>
                                    </form>
                                </details>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-slate-500">{{ t('common.empty') }}</div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourseForm from '@/Components/CourseForm.vue';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({ course: Object });
const { t } = useTranslations();

props.course.sections.forEach(section => {
    section.lessons.forEach(lesson => {
        lesson.delete_attachments = [];
        lesson.new_attachments = [];
    });
});

const form = useForm({
    title: props.course.title,
    subtitle: props.course.subtitle || '',
    description: props.course.description || '',
    price: props.course.price,
    currency: props.course.currency || 'USD',
    level: props.course.level || 'beginner',
    duration_hours: props.course.duration_hours || '',
    meta_description: props.course.meta_description || '',
    course_format: props.course.course_format || 'mixed',
    is_published: props.course.is_published,
    thumbnail: null,
});

const sectionForm = useForm({ course_id: props.course.id, title: '', description: '', order: '' });
const lessonForm = useForm({ course_section_id: props.course.sections[0]?.id || '', title: '', description: '', type: 'text', content: '', video_url: '', duration_minutes: 0, order: '', is_published: true, attachments: [] });

const submitCourse = () => form.transform(data => ({ ...data, _method: 'put' })).post(`/admin/courses/${props.course.id}`, { forceFormData: true, preserveScroll: true });
const addSection = () => sectionForm.post('/admin/sections', { preserveScroll: true, onSuccess: () => sectionForm.reset('title', 'description', 'order') });
const addLesson = () => lessonForm.post('/admin/lessons', { forceFormData: true, preserveScroll: true, onSuccess: () => lessonForm.reset('title', 'description', 'content', 'video_url', 'duration_minutes', 'order', 'attachments') });
const updateSection = (section) => router.put(`/admin/sections/${section.id}`, { title: section.title, description: section.description, order: section.order }, { preserveScroll: true });
const deleteSection = (id) => { if (confirm('Delete section?')) router.delete(`/admin/sections/${id}`, { preserveScroll: true }); };
const updateLesson = (lesson) => router.post(`/admin/lessons/${lesson.id}`, { ...lesson, attachments: lesson.new_attachments || [], delete_attachments: lesson.delete_attachments || [], _method: 'put' }, { forceFormData: true, preserveScroll: true });
const deleteLesson = (id) => { if (confirm('Delete lesson?')) router.delete(`/admin/lessons/${id}`, { preserveScroll: true }); };
</script>
