<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h5 class="text-lg font-extrabold">
                    {{ isArabic ? 'المحرر المرئي للدرس' : 'Visual Lesson Editor' }}
                </h5>
                <p class="mt-1 text-sm text-slate-500">
                    {{
                        isArabic
                            ? 'اكتب الدرس بشكل مرئي مع دعم العناوين والقوائم والروابط وكتل الأكواد والصور الداخلية.'
                            : 'Write the lesson visually with support for headings, lists, links, code blocks, and internal images.'
                    }}
                </p>
            </div>

            <div class="inline-flex rounded-2xl border border-slate-200 bg-white p-1">
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="!isPreviewMode ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="isPreviewMode = false"
                >
                    {{ isArabic ? 'تحرير' : 'Edit' }}
                </button>
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="isPreviewMode ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="isPreviewMode = true"
                >
                    {{ isArabic ? 'معاينة' : 'Preview' }}
                </button>
            </div>
        </div>

        <div class="lesson-rich-editor-frame rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="lesson-rich-editor-sticky-controls sticky top-0 z-40 rounded-t-3xl border-b border-slate-200 bg-slate-50/95 shadow-sm backdrop-blur">
                <div class="lesson-rich-editor-toolbar flex flex-wrap gap-2 p-3">
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(isParagraphActive)"
                    @click="setParagraph"
                >
                    {{ isArabic ? 'فقرة' : 'Paragraph' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('heading', { level: 2 }))"
                    @click="toggleHeading(2)"
                >
                    H2
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('heading', { level: 3 }))"
                    @click="toggleHeading(3)"
                >
                    H3
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('bold'))"
                    @click="toggleMark('bold')"
                >
                    {{ isArabic ? 'عريض' : 'Bold' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('italic'))"
                    @click="toggleMark('italic')"
                >
                    {{ isArabic ? 'مائل' : 'Italic' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('bulletList'))"
                    @click="toggleList('bulletList')"
                >
                    {{ isArabic ? 'قائمة نقطية' : 'Bullet List' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('orderedList'))"
                    @click="toggleList('orderedList')"
                >
                    {{ isArabic ? 'قائمة رقمية' : 'Numbered List' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('blockquote'))"
                    @click="toggleBlockquote"
                >
                    {{ isArabic ? 'اقتباس' : 'Blockquote' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('codeBlock'))"
                    @click="toggleCodeBlock"
                >
                    {{ isArabic ? 'كتلة كود' : 'Code Block' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(editor?.isActive('link'))"
                    @click="openLinkDialog"
                >
                    {{ isArabic ? 'رابط' : 'Link' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextDirection === 'rtl')"
                    :title="isArabic ? 'اتجاه النص من اليمين لليسار' : 'Right-to-left text direction'"
                    @click="setTextDirection('rtl')"
                >
                    RTL
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextDirection === 'ltr')"
                    :title="isArabic ? 'اتجاه النص من اليسار لليمين' : 'Left-to-right text direction'"
                    @click="setTextDirection('ltr')"
                >
                    LTR
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextAlignment === 'start')"
                    @click="setTextAlignment('start')"
                >
                    {{ isArabic ? 'بداية' : 'Start' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextAlignment === 'center')"
                    @click="setTextAlignment('center')"
                >
                    {{ isArabic ? 'وسط' : 'Center' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextAlignment === 'end')"
                    @click="setTextAlignment('end')"
                >
                    {{ isArabic ? 'نهاية' : 'End' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :class="toolbarButtonClass(activeTextAlignment === 'left')"
                    @click="setTextAlignment('left')"
                >
                    {{ isArabic ? 'يسار' : 'Left' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button bg-blue-700 text-white hover:bg-blue-800"
                    :disabled="mediaUploading"
                    @click="openImageUploader"
                >
                    {{ mediaUploading ? (isArabic ? 'جارٍ الرفع...' : 'Uploading...') : (isArabic ? 'إضافة صورة' : 'Add Image') }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :disabled="!editor?.can().chain().focus().undo().run()"
                    @click="editor?.chain().focus().undo().run()"
                >
                    {{ isArabic ? 'تراجع' : 'Undo' }}
                </button>
                <button
                    type="button"
                    class="lesson-editor-toolbar-button"
                    :disabled="!editor?.can().chain().focus().redo().run()"
                    @click="editor?.chain().focus().redo().run()"
                >
                    {{ isArabic ? 'إعادة' : 'Redo' }}
                </button>

                <input
                    ref="toolbarImageInput"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="hidden"
                    @change="handleToolbarImageSelection"
                />
                </div>

                <div
                    v-if="selectedFigure && !isPreviewMode"
                    class="border-t border-blue-100 bg-blue-50/80 p-4"
                >
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h6 class="font-extrabold text-slate-900">
                            {{ isArabic ? 'إعدادات الصورة المحددة' : 'Selected Image Settings' }}
                        </h6>
                        <p class="text-xs text-slate-500">
                            {{ isArabic ? 'اضغط على أي صورة داخل المحرر لتعديل حجمها ومكانها والتعليق الخاص بها.' : 'Click any image inside the editor to adjust its size, position, and caption.' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                        @click="removeSelectedFigure"
                    >
                        {{ isArabic ? 'إزالة من الدرس' : 'Remove from Lesson' }}
                    </button>
                </div>

                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-xs font-bold text-slate-600">{{ isArabic ? 'النص البديل' : 'Alt text' }}</label>
                        <input
                            v-model="selectedFigureForm.alt"
                            type="text"
                            class="w-full rounded-lg border-slate-300 text-sm"
                            @input="updateSelectedFigureField('alt', selectedFigureForm.alt)"
                        />
                    </div>
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-xs font-bold text-slate-600">{{ isArabic ? 'التعليق' : 'Caption' }}</label>
                        <input
                            v-model="selectedFigureForm.caption"
                            type="text"
                            class="w-full rounded-lg border-slate-300 text-sm"
                            @input="updateSelectedFigureField('caption', selectedFigureForm.caption)"
                        />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-bold text-slate-600">{{ isArabic ? 'المكان' : 'Position' }}</label>
                        <select
                            v-model="selectedFigureForm.layout"
                            class="w-full rounded-lg border-slate-300 text-sm"
                            @change="updateSelectedFigureField('layout', selectedFigureForm.layout)"
                        >
                            <option value="full">{{ isArabic ? 'عرض كامل' : 'Full' }}</option>
                            <option value="center">{{ isArabic ? 'وسط' : 'Center' }}</option>
                            <option value="start">{{ isArabic ? 'بداية السطر' : 'Start' }}</option>
                            <option value="end">{{ isArabic ? 'نهاية السطر' : 'End' }}</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-bold text-slate-600">{{ isArabic ? 'الحجم' : 'Width' }}</label>
                        <select
                            v-model="selectedFigureForm.width"
                            class="w-full rounded-lg border-slate-300 text-sm"
                            :disabled="selectedFigureForm.layout === 'full'"
                            @change="updateSelectedFigureField('width', selectedFigureForm.width)"
                        >
                            <option value="25">25%</option>
                            <option value="33">33%</option>
                            <option value="50">50%</option>
                            <option value="66">66%</option>
                            <option value="100">100%</option>
                        </select>
                    </div>
                    <label class="flex items-end gap-2 rounded-xl border border-blue-100 bg-white px-3 py-2 text-sm text-slate-700 lg:col-span-2">
                        <input
                            v-model="selectedFigureForm.wrap"
                            type="checkbox"
                            class="mb-1 rounded border-slate-300"
                            :disabled="!isWrapSupported(selectedFigureForm.layout)"
                            @change="updateSelectedFigureField('wrap', selectedFigureForm.wrap)"
                        />
                        <span>{{ isArabic ? 'التفاف النص' : 'Wrap text' }}</span>
                    </label>
                </div>
            </div>

            </div>

            <div v-if="isPreviewMode" class="p-5">
                <div
                    v-if="previewHtml"
                    class="lesson-prose text-slate-700"
                    v-html="previewHtml"
                ></div>
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-7 text-slate-500"
                >
                    {{ isArabic ? 'ابدأ الكتابة في وضع التحرير ثم عد إلى المعاينة هنا.' : 'Start writing in edit mode, then return here to preview the lesson.' }}
                </div>
            </div>

            <EditorContent
                v-else-if="editor"
                :editor="editor"
                class="lesson-rich-editor"
            />
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="mediaError" class="text-sm text-red-600">{{ mediaError }}</p>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue';
import Swal from 'sweetalert2';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { useToast } from '@/Composables/useToast';
import { LessonFigure } from '@/Components/editor/LessonFigure';
import { TextBlockAttributes } from '@/Components/editor/TextBlockAttributes';
import {
    normalizeLessonFigureAttrs,
} from '@/Components/editor/lessonFigureUtils';

const props = defineProps({
    modelValue: { type: String, default: '' },
    lessonId: { type: Number, default: null },
    existingMedia: { type: Array, default: () => [] },
    isArabic: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const toaster = useToast();
const toolbarImageInput = ref(null);
const mediaItems = ref([]);
const mediaUploading = ref(false);
const mediaError = ref('');
const isPreviewMode = ref(false);
const selectedFigure = ref(null);
const syncingSelectedFigure = ref(false);
const savedInsertionRange = ref(null);
const selectedFigureForm = reactive({
    alt: '',
    caption: '',
    layout: 'center',
    width: '50',
    wrap: false,
});

const isArabicMessage = (arabic, english) => props.isArabic ? arabic : english;

const isSafeLinkUrl = (value) => {
    const url = String(value || '').trim();
    const lower = url.toLowerCase();

    if (url === '') {
        return false;
    }

    if (url.startsWith('//')) {
        return false;
    }

    if (lower.startsWith('javascript:')
        || lower.startsWith('data:')
        || lower.startsWith('vbscript:')
        || lower.startsWith('file:')) {
        return false;
    }

    return lower.startsWith('http://')
        || lower.startsWith('https://')
        || url.startsWith('/')
        || url.startsWith('#');
};

const normalizeMedia = (items) => items.map((item) => ({
    id: item.id,
    display_url: item.display_url || item.displayUrl,
    original_name: item.original_name || item.originalName || 'image.webp',
    width: item.width || 0,
    height: item.height || 0,
    alt_text: item.alt_text || item.altText || '',
}));

const getEditorHtml = () => {
    if (!editor.value) {
        return props.modelValue || '';
    }

    return editor.value.isEmpty ? '' : editor.value.getHTML();
};

const previewHtml = computed(() => getEditorHtml());
const isParagraphActive = computed(() => {
    if (!editor.value) {
        return false;
    }

    return editor.value.isActive('paragraph')
        && !editor.value.isActive('heading')
        && !editor.value.isActive('blockquote')
        && !editor.value.isActive('codeBlock');
});
const activeTextBlockAttributes = computed(() => {
    if (!editor.value) {
        return {};
    }

    const types = ['paragraph', 'heading', 'blockquote', 'listItem'];

    for (const type of types) {
        if (editor.value.isActive(type)) {
            return editor.value.getAttributes(type) || {};
        }
    }

    return {};
});

const activeTextDirection = computed(() => activeTextBlockAttributes.value.dir || null);
const activeTextAlignment = computed(() => activeTextBlockAttributes.value.textAlign || null);

const linkAttributesForUrl = (url) => url.startsWith('http://') || url.startsWith('https://')
    ? {
        href: url,
        target: '_blank',
        rel: 'noopener noreferrer nofollow',
    }
    : {
        href: url,
        target: null,
        rel: null,
    };

const toolbarButtonClass = (isActive) => isActive
    ? 'bg-slate-900 text-white border-slate-900'
    : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100';

const isWrapSupported = (layout) => ['start', 'end'].includes(layout);

const syncSelectedFigureForm = (attrs) => {
    syncingSelectedFigure.value = true;
    selectedFigureForm.alt = attrs.alt || '';
    selectedFigureForm.caption = attrs.caption || '';
    selectedFigureForm.layout = attrs.layout || 'center';
    selectedFigureForm.width = attrs.layout === 'full' ? '100' : (attrs.width || '50');
    selectedFigureForm.wrap = Boolean(attrs.wrap) && isWrapSupported(attrs.layout);
    syncingSelectedFigure.value = false;
};

const syncSelectedFigureState = () => {
    if (!editor.value) {
        selectedFigure.value = null;
        return;
    }

    const selection = editor.value.state.selection;

    if (selection?.node?.type?.name !== 'lessonFigure') {
        selectedFigure.value = null;
        return;
    }

    const attrs = normalizeLessonFigureAttrs(selection.node.attrs);

    selectedFigure.value = {
        pos: selection.from,
        size: selection.node.nodeSize,
        attrs,
    };

    syncSelectedFigureForm(attrs);
};

const selectFigureBySrc = (src, minPos = 0) => {
    if (!editor.value || !src) {
        return;
    }

    let targetPosition = null;

    editor.value.state.doc.descendants((node, pos) => {
        if (node.type.name === 'lessonFigure' && node.attrs.src === src && pos >= minPos) {
            targetPosition = pos;
            return false;
        }

        return true;
    });

    if (targetPosition === null) {
        return;
    }

    editor.value.chain().focus().setNodeSelection(targetPosition).run();
    syncSelectedFigureState();
};

const updateSelectedFigureMarkup = (nextAttrs) => {
    if (!editor.value || !selectedFigure.value) {
        return;
    }

    const attrs = normalizeLessonFigureAttrs(nextAttrs);
    const position = selectedFigure.value.pos;

    editor.value
        .chain()
        .focus()
        .command(({ tr, dispatch }) => {
            tr.setNodeMarkup(position, undefined, attrs);

            if (dispatch) {
                dispatch(tr);
            }

            return true;
        })
        .run();
};

const updateSelectedFigureField = (field, value) => {
    if (!selectedFigure.value || syncingSelectedFigure.value) {
        return;
    }

    const nextAttrs = {
        ...selectedFigure.value.attrs,
        [field]: value,
    };

    if (field === 'layout' && value === 'full') {
        nextAttrs.width = '100';
        nextAttrs.wrap = false;
    }

    if (field === 'layout' && !isWrapSupported(value)) {
        nextAttrs.wrap = false;
    }

    if (field === 'wrap' && !isWrapSupported(selectedFigureForm.layout)) {
        nextAttrs.wrap = false;
    }

    updateSelectedFigureMarkup(nextAttrs);
};

const removeSelectedFigure = () => {
    if (!editor.value || !selectedFigure.value) {
        return;
    }

    const { pos, size } = selectedFigure.value;

    editor.value
        .chain()
        .focus()
        .command(({ tr, dispatch }) => {
            tr.delete(pos, pos + size);

            if (dispatch) {
                dispatch(tr);
            }

            return true;
        })
        .run();

    selectedFigure.value = null;
    toaster.info(isArabicMessage('تمت إزالة الصورة من محتوى الدرس.', 'The image was removed from the lesson content.'));
};

const defaultAltFromFile = (file) => String(file?.name || '')
    .replace(/\.[^.]+$/, '')
    .replace(/[-_]+/g, ' ')
    .trim();

const rememberInsertionRange = () => {
    if (!editor.value) {
        savedInsertionRange.value = null;
        return;
    }

    const { from, to } = editor.value.state.selection;
    savedInsertionRange.value = { from, to };
};

const openImageUploader = () => {
    if (!props.lessonId) {
        toaster.info(isArabicMessage('احفظ الدرس أولاً حتى تتمكن من رفع الصور داخل النص.', 'Save the lesson first so you can upload inline images.'));
        return;
    }

    if (!editor.value || mediaUploading.value) {
        return;
    }

    isPreviewMode.value = false;
    editor.value.chain().focus().run();
    rememberInsertionRange();
    mediaError.value = '';
    toolbarImageInput.value?.click();
};

const insertMediaFigureAtCursor = async (item) => {
    if (!editor.value || !item?.display_url) {
        return;
    }

    const attrs = normalizeLessonFigureAttrs({
        src: item.display_url,
        alt: item.alt_text || '',
        caption: '',
        layout: 'center',
        width: '50',
        wrap: false,
    });

    const range = savedInsertionRange.value;
    const minPosition = range?.from ?? editor.value.state.selection.from;
    const content = [
        { type: 'lessonFigure', attrs },
        { type: 'paragraph' },
    ];

    if (range) {
        editor.value.chain().focus().insertContentAt(range, content).run();
    } else {
        editor.value.chain().focus().insertContent(content).run();
    }

    await nextTick();
    selectFigureBySrc(attrs.src, minPosition);
    savedInsertionRange.value = null;
};

const uploadAndInsertImage = async (file) => {
    if (!props.lessonId || !file) {
        return;
    }

    const payload = new FormData();
    payload.append('image', file);
    payload.append('alt_text', defaultAltFromFile(file));

    mediaUploading.value = true;
    mediaError.value = '';

    try {
        const { data } = await window.axios.post(`/admin/lessons/${props.lessonId}/media`, payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const item = normalizeMedia([data])[0];
        mediaItems.value = [item, ...mediaItems.value];
        await insertMediaFigureAtCursor(item);
        toaster.success(isArabicMessage('تم رفع الصورة وإدراجها في موضع المؤشر.', 'The image was uploaded and inserted at the cursor position.'));
    } catch (error) {
        mediaError.value = error?.response?.data?.errors?.image?.[0]
            || error?.response?.data?.message
            || isArabicMessage('تعذر رفع هذه الصورة.', 'Could not upload this image.');
    } finally {
        mediaUploading.value = false;

        if (toolbarImageInput.value) {
            toolbarImageInput.value.value = '';
        }
    }
};

const handleToolbarImageSelection = async (event) => {
    const file = event.target.files?.[0] || null;

    if (!file) {
        return;
    }

    await uploadAndInsertImage(file);
};

const setParagraph = () => editor.value?.chain().focus().setParagraph().run();
const toggleHeading = (level) => editor.value?.chain().focus().toggleHeading({ level }).run();
const toggleMark = (mark) => editor.value?.chain().focus()[`toggle${mark.charAt(0).toUpperCase()}${mark.slice(1)}`]().run();
const toggleList = (listType) => {
    if (!editor.value) {
        return;
    }

    if (listType === 'bulletList') {
        editor.value.chain().focus().toggleBulletList().run();
        return;
    }

    editor.value.chain().focus().toggleOrderedList().run();
};
const toggleBlockquote = () => editor.value?.chain().focus().toggleBlockquote().run();
const toggleCodeBlock = () => editor.value?.chain().focus().toggleCodeBlock().run();
const setTextDirection = (direction) => editor.value?.chain().focus().setTextDirection(direction).run();
const setTextAlignment = (alignment) => editor.value?.chain().focus().setTextAlignment(alignment).run();

const openLinkDialog = async () => {
    if (!editor.value) {
        return;
    }

    const currentUrl = editor.value.getAttributes('link').href || '';
    const result = await Swal.fire({
        title: props.isArabic ? 'إدارة الرابط' : 'Insert or Edit Link',
        input: 'text',
        inputValue: currentUrl,
        inputLabel: props.isArabic ? 'الرابط' : 'URL',
        inputPlaceholder: props.isArabic ? 'https://example.com أو /courses/slug' : 'https://example.com or /courses/slug',
        confirmButtonText: props.isArabic ? 'تطبيق' : 'Apply',
        cancelButtonText: props.isArabic ? 'إلغاء' : 'Cancel',
        showCancelButton: true,
        reverseButtons: props.isArabic,
        didOpen: (popup) => {
            popup.setAttribute('dir', props.isArabic ? 'rtl' : 'ltr');
        },
        preConfirm: (value) => {
            const url = String(value || '').trim();

            if (url === '') {
                return '';
            }

            if (!isSafeLinkUrl(url)) {
                Swal.showValidationMessage(
                    props.isArabic
                        ? 'يسمح فقط بروابط http و https والروابط الداخلية الآمنة.'
                        : 'Only safe http, https, and internal links are allowed.',
                );

                return false;
            }

            return url;
        },
    });

    if (!result.isConfirmed) {
        return;
    }

    const url = String(result.value || '').trim();

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    const attrs = linkAttributesForUrl(url);
    const selectedText = editor.value.state.doc.textBetween(
        editor.value.state.selection.from,
        editor.value.state.selection.to,
        ' ',
    ).trim();

    if (selectedText) {
        editor.value.chain().focus().extendMarkRange('link').setLink(attrs).run();
        return;
    }

    editor.value.chain().focus().insertContent({
        type: 'text',
        text: url,
        marks: [
            {
                type: 'link',
                attrs,
            },
        ],
    }).run();
};

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            codeBlock: {
                HTMLAttributes: {
                    class: 'lesson-code-block',
                },
            },
        }),
        Link.configure({
            openOnClick: false,
            autolink: false,
            linkOnPaste: false,
        }),
        Placeholder.configure({
            placeholder: isArabicMessage(
                'ابدأ كتابة محتوى الدرس هنا...',
                'Start writing the lesson content here...',
            ),
        }),
        TextBlockAttributes,
        LessonFigure,
    ],
    content: props.modelValue || '',
    editorProps: {
        attributes: {
            class: 'lesson-rich-editor__surface lesson-prose',
            dir: props.isArabic ? 'rtl' : 'ltr',
        },
    },
    onCreate: ({ editor: editorInstance }) => {
        emit('update:modelValue', editorInstance.isEmpty ? '' : editorInstance.getHTML());
        syncSelectedFigureState();
    },
    onUpdate: ({ editor: editorInstance }) => {
        emit('update:modelValue', editorInstance.isEmpty ? '' : editorInstance.getHTML());
        syncSelectedFigureState();
    },
    onSelectionUpdate: () => {
        syncSelectedFigureState();
    },
});

watch(() => props.modelValue, (value) => {
    if (!editor.value) {
        return;
    }

    const nextHtml = value || '';
    const currentHtml = editor.value.isEmpty ? '' : editor.value.getHTML();

    if (currentHtml === nextHtml) {
        return;
    }

    editor.value.commands.setContent(nextHtml, false);
    syncSelectedFigureState();
});

watch(() => props.existingMedia, (items) => {
    mediaItems.value = normalizeMedia(items);
}, { immediate: true, deep: true });

watch(() => selectedFigureForm.layout, (layout) => {
    if (layout === 'full') {
        selectedFigureForm.width = '100';
        selectedFigureForm.wrap = false;
    }

    if (!isWrapSupported(layout)) {
        selectedFigureForm.wrap = false;
    }
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>
