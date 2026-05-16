<template>
    <div class="space-y-6">
        <!-- Lesson Type Selector -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-3">Lesson Type</label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.type" value="text" class="rounded" />
                    <span class="text-sm">📝 Text Only</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.type" value="video" class="rounded" />
                    <span class="text-sm">🎬 Video Only</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.type" value="mixed" class="rounded" />
                    <span class="text-sm">📚 Mixed</span>
                </label>
            </div>
        </div>

        <!-- Title Input -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Title</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                placeholder="Lesson title"
            />
        </div>

        <!-- Description Input -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Description (short summary)</label>
            <textarea
                v-model="form.description"
                rows="2"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                placeholder="Brief description of this lesson"
            />
        </div>

        <!-- Duration Input -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Duration (minutes)</label>
            <input
                v-model.number="form.duration_minutes"
                type="number"
                min="1"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                placeholder="15"
            />
        </div>

        <!-- Video Section -->
        <div v-if="form.type !== 'text'" class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Video Configuration</h3>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Video URL</label>
                <input
                    v-model="form.video_url"
                    type="text"
                    class="w-full rounded-lg border"
                    :class="videoError ? 'border-red-500' : 'border-gray-300'"
                    placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..."
                    @blur="parseVideoUrl"
                />
                <p v-if="videoError" class="mt-1 text-sm text-red-600">{{ videoError }}</p>
                <p v-if="videoParsed && !videoError" class="mt-1 text-sm text-green-600">
                    ✓ {{ videoParsed.video_provider }} video detected (ID: {{ videoParsed.video_id }})
                </p>
            </div>

            <!-- Video Preview -->
            <div v-if="videoParsed && !videoError" class="mb-4">
                <p class="text-sm font-semibold text-gray-900 mb-2">Preview</p>
                <div class="aspect-video rounded-lg overflow-hidden bg-gray-100">
                    <iframe
                        v-if="videoParsed.video_provider === 'youtube'"
                        :src="`https://www.youtube-nocookie.com/embed/${videoParsed.video_id}`"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        class="w-full h-full"
                    />
                    <iframe
                        v-else-if="videoParsed.video_provider === 'vimeo'"
                        :src="`https://player.vimeo.com/video/${videoParsed.video_id}`"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                        class="w-full h-full"
                    />
                </div>
            </div>
        </div>

        <!-- Content Editor -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                {{ form.type === 'video' ? 'Video Notes' : form.type === 'text' ? 'Lesson Content' : 'Lesson Content' }}
            </h3>
            <p class="text-xs text-gray-600 mb-2">
                Supports: headings, paragraphs, lists, links, blockquotes, and code blocks. No scripts or iframes.
            </p>
            <textarea
                v-model="form.content"
                rows="12"
                class="w-full font-mono rounded-lg border border-gray-300 px-3 py-2 text-sm"
                placeholder="<h2>Lesson Title</h2>&#10;<p>Your content here...</p>&#10;<pre><code>code example</code></pre>"
            />
        </div>

        <!-- Content Preview -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Preview</h3>
            <div class="prose prose-sm max-w-none bg-white p-6 rounded-lg border border-gray-200" v-html="form.content" />
        </div>

        <!-- Attachments Section -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Attachments</h3>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Upload File</label>
                <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                    <input
                        type="file"
                        class="hidden"
                        ref="fileInput"
                        @change="handleFileUpload"
                        accept=".pdf,.zip,.txt,.md,.doc,.docx,.xlsx,.png,.jpg,.jpeg,.webp"
                    />
                    <button
                        @click="$refs.fileInput.click()"
                        class="text-blue-600 hover:text-blue-700 font-semibold"
                    >
                        Choose File
                    </button>
                    <p class="text-xs text-gray-600 mt-2">
                        PDF, ZIP, DOC, DOCX, XLSX, images (PNG, JPG, WEBP)
                    </p>
                </div>
            </div>

            <!-- Existing Attachments -->
            <div v-if="attachments.length > 0" class="space-y-2">
                <p class="text-sm font-semibold text-gray-900">Attached Files</p>
                <ul class="space-y-2">
                    <li
                        v-for="attachment in attachments"
                        :key="attachment.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <span class="text-sm text-gray-700">
                            📎 {{ attachment.file_name }} ({{ formatFileSize(attachment.file_size) }})
                        </span>
                        <button
                            @click="deleteAttachment(attachment.id)"
                            class="text-red-600 hover:text-red-700 text-sm font-semibold"
                        >
                            Delete
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Publishing Status -->
        <div class="border-t pt-6">
            <label class="flex items-center gap-3">
                <input v-model="form.status" type="radio" value="draft" class="rounded" />
                <span class="text-sm font-semibold text-gray-900">Draft (hidden from students)</span>
            </label>
            <label class="flex items-center gap-3 mt-2">
                <input v-model="form.status" type="radio" value="published" class="rounded" />
                <span class="text-sm font-semibold text-gray-900">Published (visible to enrolled students)</span>
            </label>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-3 pt-4 border-t">
            <button
                @click="$emit('save')"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2 font-semibold text-white hover:bg-blue-700 transition"
            >
                Save Lesson
            </button>
            <button
                @click="$emit('cancel')"
                class="inline-flex items-center justify-center rounded-lg bg-gray-300 px-6 py-2 font-semibold text-gray-900 hover:bg-gray-400 transition"
            >
                Cancel
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    attachments: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'save', 'cancel', 'upload-file', 'delete-attachment']);

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const fileInput = ref(null);
const videoError = ref('');
const videoParsed = ref(null);

const parseVideoUrl = async () => {
    if (!form.value.video_url || !form.value.video_url.trim()) {
        videoError.value = '';
        videoParsed.value = null;
        return;
    }

    try {
        // Parse video URL (in real app, this would call backend API)
        const response = await fetch('/api/lessons/parse-video', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ url: form.value.video_url }),
        });

        if (!response.ok) {
            const error = await response.json();
            videoError.value = error.message || 'Invalid video URL';
            videoParsed.value = null;
            return;
        }

        const data = await response.json();
        form.value.video_provider = data.video_provider;
        form.value.video_id = data.video_id;
        videoParsed.value = data;
        videoError.value = '';
    } catch (error) {
        videoError.value = 'Error parsing video URL';
        videoParsed.value = null;
    }
};

const handleFileUpload = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    emit('upload-file', { file, formData });
};

const deleteAttachment = (id) => {
    emit('delete-attachment', id);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};
</script>

<style scoped>
.prose {
    --tw-prose-body: #374151;
    --tw-prose-headings: #111827;
    --tw-prose-links: #2563eb;
    --tw-prose-code: #dc2626;
    --tw-prose-pre-bg: #f3f4f6;
}
</style>
