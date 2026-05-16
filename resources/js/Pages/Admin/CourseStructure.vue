<template>
    <Head title="Course Structure" />
    <AdminLayout>
        <section class="page-shell">
            <div class="page-container">
                <div class="mb-8">
                    <Link href="/admin/courses" class="text-blue-600 hover:text-blue-700 text-sm">← Back to Courses</Link>
                </div>

                <div class="mb-8">
                    <h1 class="page-title mb-2">{{ course.title }}</h1>
                    <p class="text-gray-600">Manage course structure, sections, and lessons</p>
                </div>

                <!-- Course Status Badge -->
                <div class="mb-6">
                    <span
                        :class="statusBadgeClass"
                        class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                    >
                        {{ course.status.toUpperCase() }}
                    </span>
                </div>

                <!-- Sections List -->
                <div class="space-y-4">
                    <div v-if="sections.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-gray-700 mb-4">No sections yet. Create your first section to start building the course.</p>
                        <button
                            @click="addSection"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                        >
                            + Add Section
                        </button>
                    </div>

                    <div
                        v-for="(section, sectionIdx) in sections"
                        :key="section.id"
                        class="surface-card p-6 rounded-lg"
                    >
                        <!-- Section Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-600 font-semibold">{{ sectionIdx + 1 }}</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ section.title }}</h3>
                                    <p class="text-sm text-gray-600">{{ section.description }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-if="sectionIdx > 0"
                                    @click="moveSection(sectionIdx, -1)"
                                    title="Move up"
                                    class="p-2 text-gray-600 hover:bg-gray-100 rounded"
                                >
                                    ↑
                                </button>
                                <button
                                    v-if="sectionIdx < sections.length - 1"
                                    @click="moveSection(sectionIdx, 1)"
                                    title="Move down"
                                    class="p-2 text-gray-600 hover:bg-gray-100 rounded"
                                >
                                    ↓
                                </button>
                                <button
                                    @click="editSection(section)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                                >
                                    ✎
                                </button>
                                <button
                                    @click="deleteSection(section.id)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- Lessons List -->
                        <div class="ml-8 space-y-2 mt-6 mb-4">
                            <div v-if="section.lessons.length === 0" class="text-center py-8 text-gray-500">
                                <p class="text-sm mb-3">No lessons in this section</p>
                                <button
                                    @click="addLesson(section.id)"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-semibold"
                                >
                                    + Add Lesson
                                </button>
                            </div>

                            <div
                                v-for="(lesson, lessonIdx) in section.lessons"
                                :key="lesson.id"
                                class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-600 text-sm">{{ lessonIdx + 1 }}.</span>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ lesson.title }}</p>
                                        <div class="flex gap-2 mt-1">
                                            <span
                                                :class="lessonTypeBadge(lesson.type)"
                                                class="text-xs px-2 py-1 rounded"
                                            >
                                                {{ lesson.type }}
                                            </span>
                                            <span
                                                :class="lessonStatusBadge(lesson.status)"
                                                class="text-xs px-2 py-1 rounded"
                                            >
                                                {{ lesson.status }}
                                            </span>
                                            <span class="text-xs text-gray-600">
                                                {{ lesson.duration_minutes }} min
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        v-if="lessonIdx > 0"
                                        @click="moveLesson(sectionIdx, lessonIdx, -1)"
                                        title="Move up"
                                        class="p-2 text-gray-600 hover:bg-gray-200 rounded"
                                    >
                                        ↑
                                    </button>
                                    <button
                                        v-if="lessonIdx < section.lessons.length - 1"
                                        @click="moveLesson(sectionIdx, lessonIdx, 1)"
                                        title="Move down"
                                        class="p-2 text-gray-600 hover:bg-gray-200 rounded"
                                    >
                                        ↓
                                    </button>
                                    <button
                                        @click="editLesson(lesson)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                                    >
                                        ✎
                                    </button>
                                    <button
                                        @click="deleteLesson(lesson.id)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <button
                                @click="addLesson(section.id)"
                                class="text-blue-600 hover:text-blue-700 text-sm font-semibold mt-3"
                            >
                                + Add Lesson
                            </button>
                        </div>
                    </div>

                    <button
                        @click="addSection"
                        v-if="sections.length > 0"
                        class="w-full text-blue-600 hover:text-blue-700 font-semibold py-4 border-2 border-dashed border-blue-300 rounded-lg"
                    >
                        + Add Section
                    </button>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    course: Object,
    sections: Array,
});

const statusBadgeClass = computed(() => {
    return {
        'bg-green-100 text-green-800': course.status === 'published',
        'bg-yellow-100 text-yellow-800': course.status === 'draft',
        'bg-gray-100 text-gray-800': course.status === 'archived',
    };
});

const lessonTypeBadge = (type) => ({
    'bg-blue-100 text-blue-800': type === 'text',
    'bg-purple-100 text-purple-800': type === 'video',
    'bg-indigo-100 text-indigo-800': type === 'mixed',
});

const lessonStatusBadge = (status) => ({
    'bg-green-100 text-green-800': status === 'published',
    'bg-yellow-100 text-yellow-800': status === 'draft',
});

const addSection = () => {
    // Implementation in controller
};

const addLesson = (sectionId) => {
    // Implementation in controller
};

const editSection = (section) => {
    // Implementation in controller
};

const editLesson = (lesson) => {
    // Implementation in controller
};

const deleteSection = (sectionId) => {
    // Implementation with confirmation
};

const deleteLesson = (lessonId) => {
    // Implementation with confirmation
};

const moveSection = (index, direction) => {
    // Implementation in controller
};

const moveLesson = (sectionIdx, lessonIdx, direction) => {
    // Implementation in controller
};
</script>
