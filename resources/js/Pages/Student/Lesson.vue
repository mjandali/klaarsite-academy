<template>
    <Head :title="`${lesson.title} - ${course.title}`" />
    <StudentLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <Link href="/dashboard/my-courses" class="text-blue-600 hover:text-blue-700 font-semibold">
                        ← Back to Courses
                    </Link>
                    <div class="text-sm text-gray-600">
                        {{ courseProgressPercentage }}% Complete
                    </div>
                </div>

                <!-- Course Progress Bar -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="font-semibold text-gray-900">{{ course.title }}</h2>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                            {{ publishedLessonCount }} lessons
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-2">
                        <div
                            class="bg-blue-600 h-2 rounded-full transition-all"
                            :style="{ width: courseProgressPercentage + '%' }"
                        />
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Curriculum Sidebar -->
                    <div class="lg:col-span-1 order-2 lg:order-1">
                        <div class="surface-card p-4 rounded-lg sticky top-4">
                            <h3 class="font-semibold text-gray-900 mb-4">Curriculum</h3>
                            <div class="space-y-1 max-h-96 overflow-y-auto">
                                <div
                                    v-for="section in course.sections"
                                    :key="section.id"
                                    class="mb-4"
                                >
                                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">{{ section.title }}</p>
                                    <div class="space-y-1">
                                        <button
                                            v-for="currLesson in section.lessons"
                                            :key="currLesson.id"
                                            @click="goToLesson(currLesson.id)"
                                            :class="[
                                                'w-full text-left p-2 rounded text-sm transition',
                                                lesson.id === currLesson.id
                                                    ? 'bg-blue-100 text-blue-900 font-semibold'
                                                    : 'text-gray-700 hover:bg-gray-100',
                                            ]"
                                        >
                                            <span v-if="isLessonCompleted(currLesson.id)" class="mr-1">✓</span>
                                            {{ getLessonTypeIcon(currLesson.type) }} {{ currLesson.title }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson Content -->
                    <div class="lg:col-span-3 order-1 lg:order-2">
                        <div class="surface-card rounded-lg overflow-hidden">
                            <!-- Lesson Header -->
                            <div class="border-b p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ lesson.title }}</h1>
                                        <p class="text-gray-600">{{ lesson.description }}</p>
                                    </div>
                                    <div class="flex gap-3">
                                        <span
                                            :class="lessonTypeBadge"
                                            class="text-xs font-semibold px-3 py-1 rounded-full"
                                        >
                                            {{ getLessonTypeLabel(lesson.type) }}
                                        </span>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-800">
                                            ⏱ {{ lesson.duration_minutes }} min
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Video Section -->
                            <div v-if="lesson.type !== 'text' && lesson.video_embed_url" class="aspect-video bg-black">
                                <iframe
                                    :src="lesson.video_embed_url"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    class="w-full h-full"
                                    @play="markLessonAsWatching"
                                />
                            </div>

                            <!-- Content Section -->
                            <div class="p-6">
                                <div
                                    v-if="lesson.content"
                                    class="prose prose-sm max-w-none mb-8"
                                    v-html="lesson.content"
                                />

                                <!-- Attachments -->
                                <div v-if="attachments.length > 0" class="border-t pt-6 mt-6">
                                    <h3 class="font-semibold text-gray-900 mb-4">📎 Attachments</h3>
                                    <div class="space-y-2">
                                        <a
                                            v-for="attachment in attachments"
                                            :key="attachment.id"
                                            :href="`/lesson-attachments/${attachment.id}/download`"
                                            class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition"
                                        >
                                            <span>📥</span>
                                            <span>{{ attachment.file_name }}</span>
                                            <span class="text-xs text-gray-600 ml-auto">{{ formatFileSize(attachment.file_size) }}</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Mark Complete -->
                                <div class="border-t pt-6 mt-6">
                                    <button
                                        @click="toggleLessonComplete"
                                        :class="[
                                            'px-6 py-3 rounded-lg font-semibold transition',
                                            isLessonCompleted(lesson.id)
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-blue-600 text-white hover:bg-blue-700',
                                        ]"
                                    >
                                        {{ isLessonCompleted(lesson.id) ? '✓ Completed' : 'Mark as Complete' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Navigation -->
                            <div class="border-t px-6 py-4 flex items-center justify-between bg-gray-50">
                                <button
                                    v-if="previousLesson"
                                    @click="goToLesson(previousLesson.id)"
                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold"
                                >
                                    ← {{ previousLesson.title }}
                                </button>
                                <div v-else />

                                <span class="text-sm text-gray-600">
                                    Lesson {{ lessonNumber }} of {{ publishedLessonCount }}
                                </span>

                                <button
                                    v-if="nextLesson"
                                    @click="goToLesson(nextLesson.id)"
                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold"
                                >
                                    {{ nextLesson.title }} →
                                </button>
                                <div v-else />
                            </div>

                            <!-- Course Complete Message -->
                            <div
                                v-if="isCourseCoplete && !nextLesson"
                                class="bg-green-50 border-t border-green-200 p-6 text-center"
                            >
                                <p class="text-2xl font-bold text-green-800 mb-2">🎉 Congratulations!</p>
                                <p class="text-green-700 mb-4">You've completed all lessons in {{ course.title }}</p>
                                <Link
                                    href="/dashboard/my-courses"
                                    class="text-blue-600 hover:text-blue-700 font-semibold"
                                >
                                    Browse More Courses →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';

const props = defineProps({
    course: Object,
    lesson: Object,
    attachments: Array,
    progress: Array,
});

const publishedLessons = computed(() => {
    return props.course.sections
        .flatMap(s => s.lessons.filter(l => l.status === 'published'))
        .sort((a, b) => {
            const orderA = props.course.sections.findIndex(s => s.lessons.includes(a));
            const orderB = props.course.sections.findIndex(s => s.lessons.includes(b));
            if (orderA !== orderB) return orderA - orderB;
            return a.order - b.order;
        });
});

const publishedLessonCount = computed(() => publishedLessons.value.length);

const lessonNumber = computed(() => {
    return publishedLessons.value.findIndex(l => l.id === props.lesson.id) + 1;
});

const courseProgressPercentage = computed(() => {
    if (publishedLessonCount.value === 0) return 0;
    const completed = props.progress.filter(p => p.is_completed).length;
    return Math.round((completed / publishedLessonCount.value) * 100);
});

const isCourseCoplete = computed(() => {
    return courseProgressPercentage.value === 100;
});

const previousLesson = computed(() => {
    const idx = publishedLessons.value.findIndex(l => l.id === props.lesson.id);
    return idx > 0 ? publishedLessons.value[idx - 1] : null;
});

const nextLesson = computed(() => {
    const idx = publishedLessons.value.findIndex(l => l.id === props.lesson.id);
    return idx < publishedLessons.value.length - 1 ? publishedLessons.value[idx + 1] : null;
});

const lessonTypeBadge = computed(() => {
    const badges = {
        text: 'bg-blue-100 text-blue-800',
        video: 'bg-purple-100 text-purple-800',
        mixed: 'bg-indigo-100 text-indigo-800',
    };
    return badges[props.lesson.type] || 'bg-gray-100 text-gray-800';
});

const getLessonTypeIcon = (type) => {
    const icons = { text: '📝', video: '🎬', mixed: '📚' };
    return icons[type] || '📄';
};

const getLessonTypeLabel = (type) => {
    const labels = { text: 'Text', video: 'Video', mixed: 'Mixed' };
    return labels[type] || 'Lesson';
};

const isLessonCompleted = (lessonId) => {
    return props.progress.some(p => p.lesson_id === lessonId && p.is_completed);
};

const goToLesson = (lessonId) => {
    window.location.href = `/lesson/${lessonId}`;
};

const toggleLessonComplete = async () => {
    // API call to mark lesson as complete/incomplete
};

const markLessonAsWatching = () => {
    // API call to track video watch time
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
