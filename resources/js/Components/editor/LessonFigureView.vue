<template>
    <NodeViewWrapper
        as="figure"
        class="lesson-editor-figure"
        :class="[figureClasses, { 'lesson-editor-figure--selected': selected }]"
        role="button"
        tabindex="0"
        @click="selectThisFigure"
        @keydown.enter.prevent="selectThisFigure"
        @keydown.space.prevent="selectThisFigure"
    >
        <div class="lesson-editor-figure__image-frame">
            <img
                :src="node.attrs.src"
                :alt="node.attrs.alt || ''"
                class="lesson-editor-figure__image"
                draggable="false"
            />
        </div>

        <figcaption
            v-if="node.attrs.caption"
            class="lesson-editor-figure__caption"
        >
            {{ node.attrs.caption }}
        </figcaption>
        <figcaption
            v-else
            class="lesson-editor-figure__caption lesson-editor-figure__caption--placeholder"
        >
            {{ isArabic ? 'حدد الصورة لتعديل التعليق والحجم والمكان' : 'Select the image to edit caption, size, and position' }}
        </figcaption>
    </NodeViewWrapper>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import { buildLessonFigureClasses } from '@/Components/editor/lessonFigureUtils';

const props = defineProps(nodeViewProps);

const page = usePage();
const isArabic = computed(() => page.props.locale?.current === 'ar');
const figureClasses = computed(() => buildLessonFigureClasses(props.node.attrs));

const selectThisFigure = () => {
    if (typeof props.getPos !== 'function') {
        return;
    }

    props.editor?.chain().focus().setNodeSelection(props.getPos()).run();
};
</script>
