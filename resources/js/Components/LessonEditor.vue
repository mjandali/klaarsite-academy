<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-xl font-extrabold">{{ title }}</h3>
                <p class="mt-1 text-sm text-slate-500">
                    {{ isArabic ? 'أنشئ درساً كتابياً أو مرئياً أو مختلطاً مع دعم المعاينة والمرفقات والصور الداخلية.' : 'Create text, video, or mixed lessons with preview support, attachments, and internal lesson images.' }}
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
                        {{ isArabic ? 'مدعوم حالياً: YouTube و Vimeo فقط.' : 'Supported providers: YouTube and Vimeo only.' }}
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
            <p v-if="videoMessage && !videoError" class="mt-2 text-sm text-emerald-600">{{ videoMessage }}</p>
            <p v-if="videoError" class="mt-2 text-sm text-red-600">{{ videoError }}</p>
            <p v-if="form.errors.video_url" class="mt-2 text-sm text-red-600">{{ form.errors.video_url }}</p>

            <div v-if="previewUrl" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-black">
                <div class="aspect-video">
                    <iframe :src="previewUrl" class="h-full w-full" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>
        </div>

        <div class="space-y-3">
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
                        {{ isArabic ? 'مسموح: العناوين، القوائم، الروابط، الاقتباسات، وكتل الأكواد. كما يمكن إدراج صور الدرس الداخلية بشكل آمن.' : 'Supports headings, lists, links, blockquotes, code blocks, and safe internal lesson images.' }}
                    </p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ isArabic ? 'الترتيب عبر الأسهم' : 'Ordered with arrows' }}
                </div>
            </div>

            <textarea
                ref="contentEditor"
                v-model="form.content"
                rows="12"
                class="w-full rounded-2xl border-slate-300 font-mono text-sm"
                placeholder="<h2>Lesson Title</h2>&#10;<p>Your content here...</p>&#10;<figure><img src=&quot;/lesson-media/1&quot; alt=&quot;&quot;><figcaption></figcaption></figure>&#10;<pre><code>code example</code></pre>"
            ></textarea>
            <p v-if="form.errors.content" class="text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="text-lg font-extrabold">{{ isArabic ? 'صور الدرس' : 'Lesson Images' }}</h4>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ isArabic ? 'الأنواع المسموحة: JPG و PNG و WEBP. سيتم تحويل الصورة إلى WebP وتخزينها داخلياً.' : 'Allowed types: JPG, PNG, and WEBP. Images are converted to WebP and stored privately.' }}
                    </p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ lessonId ? (isArabic ? 'جاهز للرفع' : 'Ready to upload') : (isArabic ? 'احفظ الدرس أولاً' : 'Save the lesson first') }}
                </div>
            </div>

            <div v-if="lessonId" class="mt-4 space-y-4">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <input
                        ref="mediaInput"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full rounded-lg border border-slate-300 p-2"
                        @change="handleMediaSelection"
                    />
                    <button
                        type="button"
                        class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white transition hover:bg-slate-800"
                        :disabled="!selectedImage || mediaUploading"
                        @click="uploadImage"
                    >
                        {{ mediaUploading ? (isArabic ? 'جارٍ الرفع...' : 'Uploading...') : (isArabic ? 'رفع الصورة' : 'Upload Image') }}
                    </button>
                </div>

                <p v-if="mediaMessage && !mediaError" class="text-sm text-emerald-600">{{ mediaMessage }}</p>
                <p v-if="mediaError" class="text-sm text-red-600">{{ mediaError }}</p>

                <div v-if="mediaItems.length" class="grid gap-4 md:grid-cols-2">
                    <article v-for="item in mediaItems" :key="item.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <img :src="item.display_url" :alt="item.alt_text || ''" class="h-48 w-full object-cover" />
                        <div class="space-y-3 p-4">
                            <div>
                                <p class="truncate font-bold text-slate-800">{{ item.original_name }}</p>
                                <p class="text-xs text-slate-500">{{ item.width || 0 }} × {{ item.height || 0 }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                    @click="insertMediaSnippet(item)"
                                >
                                    {{ isArabic ? 'إدراج في المحتوى' : 'Insert into content' }}
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                    @click="copyMediaSnippet(item)"
                                >
                                    {{ isArabic ? 'نسخ المقطع' : 'Copy snippet' }}
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div v-else class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm leading-7 text-slate-500">
                {{ isArabic ? 'يمكنك رفع صور الدرس بعد إنشاء الدرس لأول مرة. احفظ الدرس ثم افتحه للتعديل وسيظهر قسم الرفع هنا.' : 'You can upload lesson images after creating the lesson for the first time. Save the lesson, reopen it for editing, and the upload section will be available here.' }}
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 p-5">
            <h4 class="text-lg font-extrabold">{{ isArabic ? 'مرفقات الدرس' : 'Lesson Attachments' }}</h4>
            <p class="mt-1 text-sm text-slate-500">
                {{ isArabic ? 'الأنواع المسموحة: PDF, ZIP, TXT, MD, DOC, DOCX, XLSX, PNG, JPG, WEBP.' : 'Allowed types: PDF, ZIP, TXT, MD, DOC, DOCX, XLSX, PNG, JPG, WEBP.' }}
            </p>

            <input
                type="file"
                multiple
                accept=".pdf,.zip,.txt,.md,.doc,.docx,.xlsx,.png,.jpg,.jpeg,.webp"
                class="mt-4 w-full rounded-lg border border-slate-300 p-2"
                @input="form.attachments = [...$event.target.files]"
            />
            <p v-if="form.errors.attachments" class="mt-2 text-sm text-red-600">{{ form.errors.attachments }}</p>
            <p v-if="form.errors['attachments.0']" class="mt-2 text-sm text-red-600">{{ form.errors['attachments.0'] }}</p>

            <div v-if="attachments.length" class="mt-5 space-y-3">
                <p class="font-semibold text-slate-800">{{ isArabic ? 'المرفقات الحالية' : 'Current Attachments' }}</p>
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
import { useTranslations } from '@/Composables/useTranslations';

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
const isArabic = computed(() => page.props.locale.current === 'ar');
const contentEditor = ref(null);
const mediaInput = ref(null);
const selectedImage = ref(null);
const mediaItems = ref([]);
const mediaUploading = ref(false);
const mediaError = ref('');
const mediaMessage = ref('');
const videoError = ref('');
const videoMessage = ref('');
const previewUrl = ref('');

const statusLabel = computed(() => props.form.status === 'published' ? t('admin.published') : t('admin.draft'));
const statusBadgeClass = computed(() => props.form.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700');

const normalizeMedia = (items) => items.map((item) => ({
    id: item.id,
    display_url: item.display_url || item.displayUrl,
    original_name: item.original_name || item.originalName || 'image.webp',
    width: item.width || 0,
    height: item.height || 0,
    alt_text: item.alt_text || '',
}));

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
    videoMessage.value = '';
    previewUrl.value = '';
};

const parseVideoUrl = async () => {
    if (props.form.type === 'text') {
        clearVideoState();
        props.form.video_provider = null;
        props.form.video_id = null;
        props.form.video_url = null;

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
        videoMessage.value = isArabic.value
            ? `تم التعرف على فيديو ${data.video_provider}.`
            : `${data.video_provider} video detected successfully.`;
    } catch (error) {
        props.form.video_provider = null;
        props.form.video_id = null;
        previewUrl.value = '';
        videoMessage.value = '';
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

const handleMediaSelection = (event) => {
    selectedImage.value = event.target.files?.[0] || null;
    mediaError.value = '';
    mediaMessage.value = '';
};

const mediaSnippet = (item) => `<figure><img src="${item.display_url}" alt="${item.alt_text || ''}"><figcaption></figcaption></figure>`;

const insertAtCursor = (snippet) => {
    const current = props.form.content || '';
    const textarea = contentEditor.value;

    if (!textarea) {
        props.form.content = `${current}${current ? '\n\n' : ''}${snippet}`;

        return;
    }

    const start = textarea.selectionStart ?? current.length;
    const end = textarea.selectionEnd ?? current.length;
    const before = current.slice(0, start);
    const after = current.slice(end);
    const prefix = before && !before.endsWith('\n') ? '\n\n' : '';
    const suffix = after && !after.startsWith('\n') ? '\n\n' : '';

    props.form.content = `${before}${prefix}${snippet}${suffix}${after}`;
};

const insertMediaSnippet = (item) => {
    insertAtCursor(mediaSnippet(item));
    mediaMessage.value = isArabic.value ? 'تم إدراج الصورة داخل محتوى الدرس.' : 'The image snippet was inserted into the lesson content.';
    mediaError.value = '';
};

const copyMediaSnippet = async (item) => {
    const snippet = mediaSnippet(item);

    if (!navigator?.clipboard?.writeText) {
        mediaError.value = isArabic.value ? 'المتصفح الحالي لا يدعم نسخ المقطع تلقائياً.' : 'Clipboard copying is not available in this browser.';

        return;
    }

    await navigator.clipboard.writeText(snippet);
    mediaMessage.value = isArabic.value ? 'تم نسخ مقطع الصورة.' : 'The image snippet was copied.';
    mediaError.value = '';
};

const uploadImage = async () => {
    if (!props.lessonId || !selectedImage.value) {
        return;
    }

    const payload = new FormData();
    payload.append('image', selectedImage.value);

    mediaUploading.value = true;
    mediaError.value = '';
    mediaMessage.value = '';

    try {
        const { data } = await window.axios.post(`/admin/lessons/${props.lessonId}/media`, payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const item = normalizeMedia([data])[0];
        mediaItems.value = [item, ...mediaItems.value];
        mediaMessage.value = isArabic.value ? 'تم رفع الصورة بنجاح.' : 'The image was uploaded successfully.';
        selectedImage.value = null;

        if (mediaInput.value) {
            mediaInput.value.value = '';
        }
    } catch (error) {
        mediaError.value = error?.response?.data?.errors?.image?.[0]
            || error?.response?.data?.message
            || (isArabic.value ? 'تعذر رفع الصورة.' : 'Could not upload this image.');
    } finally {
        mediaUploading.value = false;
    }
};

watch(() => props.media, (items) => {
    mediaItems.value = normalizeMedia(items);
}, { immediate: true, deep: true });

watch(() => props.form.video_url, () => {
    videoError.value = '';
    videoMessage.value = '';
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
