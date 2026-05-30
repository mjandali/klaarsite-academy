<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-xl font-extrabold">{{ title }}</h3>
                <p class="mt-1 text-sm text-slate-500">
                    {{ isArabic ? 'أنشئ درساً كتابياً أو مرئياً أو مختلطاً مع محرر بصري آمن، ومعاينة، ومرفقات، وصور داخلية.' : 'Create text, video, or mixed lessons with a safe visual editor, preview, attachments, and internal lesson images.' }}
                </p>
            </div>
            <div class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="statusBadgeClass">
                {{ statusLabel }}
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'القسم' : 'Section' }}</label>
                <select v-model="form.course_section_id" class="w-full rounded-lg border-slate-300">
                    <option disabled value="">{{ isArabic ? 'اختر قسماً' : 'Choose a section' }}</option>
                    <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.title }}</option>
                </select>
                <p v-if="form.errors.course_section_id" class="mt-1 text-sm text-red-600">{{ form.errors.course_section_id }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'حالة الدرس' : 'Lesson Status' }}</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="rounded-2xl border p-3 transition" :class="form.status === 'draft' ? 'border-slate-900 bg-slate-50' : 'border-slate-200'">
                        <input v-model="form.status" type="radio" value="draft" class="sr-only" />
                        <span class="font-semibold">{{ t('admin.draft') }}</span>
                    </label>
                    <label class="rounded-2xl border p-3 transition" :class="form.status === 'published' ? 'border-emerald-600 bg-emerald-50' : 'border-slate-200'">
                        <input v-model="form.status" type="radio" value="published" class="sr-only" />
                        <span class="font-semibold">{{ t('admin.published') }}</span>
                    </label>
                </div>
                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'عنوان الدرس' : 'Lesson Title' }}</label>
                <input v-model="form.title" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'نوع الدرس' : 'Lesson Type' }}</label>
                <div class="grid gap-3 sm:grid-cols-3">
                    <label class="rounded-2xl border p-3 transition" :class="form.type === 'text' ? 'border-blue-600 bg-blue-50' : 'border-slate-200'">
                        <input v-model="form.type" type="radio" value="text" class="sr-only" />
                        <span class="font-semibold">{{ isArabic ? 'كتابي' : 'Text' }}</span>
                    </label>
                    <label class="rounded-2xl border p-3 transition" :class="form.type === 'video' ? 'border-blue-600 bg-blue-50' : 'border-slate-200'">
                        <input v-model="form.type" type="radio" value="video" class="sr-only" />
                        <span class="font-semibold">{{ isArabic ? 'مرئي' : 'Video' }}</span>
                    </label>
                    <label class="rounded-2xl border p-3 transition" :class="form.type === 'mixed' ? 'border-blue-600 bg-blue-50' : 'border-slate-200'">
                        <input v-model="form.type" type="radio" value="mixed" class="sr-only" />
                        <span class="font-semibold">{{ isArabic ? 'مختلط' : 'Mixed' }}</span>
                    </label>
                </div>
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'مدة الدرس بالدقائق' : 'Duration Minutes' }}</label>
                <input v-model="form.duration_minutes" type="number" min="0" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.duration_minutes" class="mt-1 text-sm text-red-600">{{ form.errors.duration_minutes }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'وصف قصير' : 'Short Description' }}</label>
                <textarea v-model="form.description" rows="3" class="w-full rounded-lg border-slate-300"></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>
        </div>

        <div v-if="form.type !== 'text'" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="text-lg font-extrabold">{{ isArabic ? 'فيديو الدرس' : 'Lesson Video' }}</h4>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ isArabic ? 'المنصات المدعومة حالياً: YouTube و Vimeo فقط.' : 'Supported providers: YouTube and Vimeo only.' }}
                    </p>
                </div>
                <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white" @click="parseVideoUrl">
                    {{ isArabic ? 'تحقق من الرابط' : 'Check URL' }}
                </button>
            </div>

            <label class="mb-2 block font-bold">{{ isArabic ? 'رابط الفيديو' : 'Video URL' }}</label>
            <input
                v-model="form.video_url"
                type="text"
                class="w-full rounded-lg border-slate-300"
                placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..."
                @blur="parseVideoUrl"
            />
            <p class="mt-1 text-xs text-slate-500">
                {{ isArabic ? 'إذا كان الرابط غير مدعوم فسيظهر لك الخطأ هنا قبل الحفظ وبعده.' : 'Unsupported providers are rejected with a clear error before and after save.' }}
            </p>
            <p v-if="videoError" class="mt-2 text-sm text-red-600">{{ videoError }}</p>
            <p v-if="form.errors.video_url" class="mt-2 text-sm text-red-600">{{ form.errors.video_url }}</p>

            <div v-if="previewUrl" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-black">
                <div class="aspect-video">
                    <iframe :src="previewUrl" class="h-full w-full" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-lg font-extrabold">
                        {{
                            form.type === 'video'
                                ? (isArabic ? 'ملاحظات الفيديو' : 'Video Notes')
                                : (isArabic ? 'محتوى الدرس' : 'Lesson Content')
                        }}
                    </h4>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ isArabic ? 'اكتب الدرس داخل محرر بصري احترافي، وسيتم تعقيم المحتوى آلياً عند الحفظ.' : 'Write the lesson inside a professional visual editor. Content is sanitized automatically on save.' }}
                    </p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ isArabic ? 'محتوى آمن ومعقّم' : 'Sanitized content' }}
                </div>
            </div>

            <LessonRichTextEditor
                :model-value="form.content"
                :lesson-id="lessonId"
                :existing-media="media"
                :is-arabic="isArabic"
                :error="form.errors.content"
                @update:model-value="updateContent"
            />
        </div>

        <div class="rounded-2xl border border-slate-200 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="text-lg font-extrabold">{{ isArabic ? 'مرفقات الدرس' : 'Lesson Attachments' }}</h4>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ isArabic ? 'الأنواع المسموحة: PDF و ZIP و TXT و MD و DOC و DOCX و XLSX و PNG و JPG و JPEG و WEBP.' : 'Allowed types: PDF, ZIP, TXT, MD, DOC, DOCX, XLSX, PNG, JPG, JPEG, and WEBP.' }}
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <input
                    type="file"
                    multiple
                    class="w-full rounded-lg border border-slate-300 p-2"
                    @input="form.attachments = $event.target.files"
                />
                <p v-if="form.errors.attachments" class="mt-2 text-sm text-red-600">{{ form.errors.attachments }}</p>
                <p v-if="form.errors['attachments.0']" class="mt-2 text-sm text-red-600">{{ form.errors['attachments.0'] }}</p>
            </div>

            <div v-if="attachments.length" class="mt-5 space-y-3">
                <label
                    v-for="attachment in attachments"
                    :key="attachment.id"
                    class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3"
                >
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-800">{{ attachment.file_name }}</p>
                        <p class="text-xs text-slate-500">{{ formatFileSize(attachment.file_size) }}</p>
                    </div>
                    <span class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="form.delete_attachments" type="checkbox" :value="attachment.id" class="rounded border-slate-300" />
                        {{ isArabic ? 'حذف' : 'Delete' }}
                    </span>
                </label>
                <p class="text-xs text-slate-500">
                    {{ isArabic ? 'سيتم حذف أي ملف محدد عند حفظ الدرس.' : 'Checked files will be removed when you save the lesson.' }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="$emit('cancel')"
            >
                {{ isArabic ? 'إلغاء' : 'Cancel' }}
            </button>
            <button
                type="button"
                class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white transition hover:bg-blue-800"
                :disabled="form.processing"
                @click="$emit('submit')"
            >
                {{ submitLabel }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { useTranslations } from '@/Composables/useTranslations';
import LessonRichTextEditor from '@/Components/LessonRichTextEditor.vue';

const props = defineProps({
    form: { type: Object, required: true },
    sections: { type: Array, default: () => [] },
    attachments: { type: Array, default: () => [] },
    media: { type: Array, default: () => [] },
    lessonId: { type: Number, default: null },
    title: { type: String, default: 'Lesson' },
    submitLabel: { type: String, default: 'Save Lesson' },
});

defineEmits(['submit', 'cancel']);

const page = usePage();
const { t } = useTranslations();
const toaster = useToast();
const isArabic = computed(() => page.props.locale.current === 'ar');
const videoError = ref('');
const previewUrl = ref('');

const statusLabel = computed(() => props.form.status === 'published' ? t('admin.published') : t('admin.draft'));
const statusBadgeClass = computed(() => props.form.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700');

const updateContent = (value) => {
    props.form.content = value;
    props.form.clearErrors('content');
};

const buildPreviewUrl = (provider, id) => {
    if (!provider || !id) {
        return '';
    }

    if (provider === 'youtube') {
        return `https://www.youtube-nocookie.com/embed/${id}`;
    }

    if (provider === 'vimeo') {
        return `https://player.vimeo.com/video/${id}`;
    }

    return '';
};

const clearVideoState = () => {
    videoError.value = '';
    previewUrl.value = '';
};

const parseVideoUrl = async () => {
    if (props.form.type === 'text') {
        clearVideoState();
        props.form.video_provider = null;
        props.form.video_id = null;
        props.form.video_url = '';

        return;
    }

    if (!props.form.video_url || !props.form.video_url.trim()) {
        clearVideoState();
        props.form.video_provider = null;
        props.form.video_id = null;

        return;
    }

    try {
        const { data } = await window.axios.post('/admin/lessons/parse-video', { url: props.form.video_url });

        props.form.video_provider = data.video_provider;
        props.form.video_id = data.video_id;
        previewUrl.value = buildPreviewUrl(data.video_provider, data.video_id);
        videoError.value = '';
        toaster.info(
            isArabic.value
                ? `تم التعرف على فيديو ${data.video_provider} بنجاح.`
                : `${data.video_provider} video detected successfully.`,
        );
    } catch (error) {
        props.form.video_provider = null;
        props.form.video_id = null;
        previewUrl.value = '';
        videoError.value = error?.response?.data?.message || (isArabic.value ? 'تعذر التحقق من رابط الفيديو.' : 'Could not validate this video URL.');
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB'];
    const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

    return `${(bytes / (1024 ** unitIndex)).toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
};

watch(() => props.form.video_url, () => {
    videoError.value = '';
});

watch(() => props.form.type, (type) => {
    if (type === 'text') {
        clearVideoState();
        props.form.video_url = '';
        props.form.video_provider = null;
        props.form.video_id = null;
    }
});

onMounted(() => {
    if (props.form.video_provider && props.form.video_id) {
        previewUrl.value = buildPreviewUrl(props.form.video_provider, props.form.video_id);
    }
});
</script>
