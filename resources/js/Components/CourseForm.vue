<template>
    <form @submit.prevent="$emit('submit')" class="surface-card space-y-6 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ t('admin.title') }}</label>
                <input v-model="form.title" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">Subtitle</label>
                <input v-model="form.subtitle" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.subtitle" class="mt-1 text-sm text-red-600">{{ form.errors.subtitle }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ t('admin.price') }}</label>
                <input v-model="form.price" type="number" step="0.01" min="0" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">Currency</label>
                <select v-model="form.currency" class="w-full rounded-lg border-slate-300">
                    <option>USD</option>
                    <option>EUR</option>
                    <option>GBP</option>
                </select>
                <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ t('courses.level') }}</label>
                <select v-model="form.level" class="w-full rounded-lg border-slate-300">
                    <option value="beginner">beginner</option>
                    <option value="intermediate">intermediate</option>
                    <option value="advanced">advanced</option>
                </select>
                <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">Course format</label>
                <select v-model="form.course_format" class="w-full rounded-lg border-slate-300">
                    <option value="text">Text</option>
                    <option value="video">Video</option>
                    <option value="mixed">Mixed</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Use this to describe whether the course is written, video-based, or both.</p>
                <p v-if="form.errors.course_format" class="mt-1 text-sm text-red-600">{{ form.errors.course_format }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">Duration hours</label>
                <input v-model="form.duration_hours" type="number" min="0" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.duration_hours" class="mt-1 text-sm text-red-600">{{ form.errors.duration_hours }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">Description / landing page content</label>
                <textarea
                    v-model="form.description"
                    rows="8"
                    class="w-full rounded-lg border-slate-300 font-mono text-sm"
                    placeholder="<h2>Overview</h2><p>Use safe HTML only.</p><pre><code>Code blocks are supported.</code></pre>"
                ></textarea>
                <p class="mt-1 text-xs text-slate-500">Unsafe HTML such as scripts, iframes, inline styles, and event handlers will be removed automatically.</p>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">Meta description</label>
                <input v-model="form.meta_description" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">Thumbnail</label>
                <input
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @input="form.thumbnail = $event.target.files[0]"
                    class="w-full rounded-lg border border-slate-300 p-2"
                />
                <p class="mt-1 text-xs text-slate-500">Uploaded images are converted to WebP automatically to save space.</p>
                <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-red-600">{{ form.errors.thumbnail }}</p>
            </div>

            <label class="inline-flex items-center gap-2 md:col-span-2">
                <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-blue-600" />
                <span>{{ t('admin.published') }}</span>
            </label>
        </div>

        <button class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white transition hover:bg-blue-800" :disabled="form.processing">
            {{ t('admin.save') }}
        </button>
    </form>
</template>

<script setup>
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ form: Object, submitLabel: String });
defineEmits(['submit']);

const { t } = useTranslations();
</script>
