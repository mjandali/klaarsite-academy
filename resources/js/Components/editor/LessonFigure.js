import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import LessonFigureView from '@/Components/editor/LessonFigureView.vue';
import {
    buildLessonFigureClasses,
    normalizeLessonFigureAttrs,
    parseLessonFigureClasses,
} from '@/Components/editor/lessonFigureUtils';

const extractLessonFigureAttrs = (element) => {
    const image = element.querySelector('img');

    if (!image) {
        return false;
    }

    const src = image.getAttribute('src') || '';

    if (!src.startsWith('/lesson-media/')) {
        return false;
    }

    const caption = element.querySelector('figcaption')?.textContent?.trim() || '';
    const parsed = parseLessonFigureClasses(element.getAttribute('class') || image.getAttribute('class') || '');

    return normalizeLessonFigureAttrs({
        src,
        alt: image.getAttribute('alt') || '',
        caption,
        ...parsed,
    });
};

const extractLessonFigureAttrsFromImage = (element) => {
    const src = element.getAttribute('src') || '';

    if (!src.startsWith('/lesson-media/')) {
        return false;
    }

    return normalizeLessonFigureAttrs({
        src,
        alt: element.getAttribute('alt') || '',
        ...parseLessonFigureClasses(element.getAttribute('class') || ''),
    });
};

export const LessonFigure = Node.create({
    name: 'lessonFigure',

    group: 'block',

    atom: true,

    draggable: true,

    selectable: true,

    isolating: true,

    addAttributes() {
        return {
            src: {
                default: '',
            },
            alt: {
                default: '',
            },
            caption: {
                default: '',
            },
            layout: {
                default: 'center',
            },
            width: {
                default: '50',
            },
            wrap: {
                default: false,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'figure',
                getAttrs: (element) => extractLessonFigureAttrs(element),
            },
            {
                tag: 'img[src^="/lesson-media/"]',
                getAttrs: (element) => extractLessonFigureAttrsFromImage(element),
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        const attrs = normalizeLessonFigureAttrs(HTMLAttributes);
        const figureAttributes = mergeAttributes({
            class: buildLessonFigureClasses(attrs),
        });
        const imageAttributes = {
            src: attrs.src,
            alt: attrs.alt || '',
        };

        if (attrs.caption) {
            return ['figure', figureAttributes, ['img', imageAttributes], ['figcaption', {}, attrs.caption]];
        }

        return ['figure', figureAttributes, ['img', imageAttributes]];
    },

    addNodeView() {
        return VueNodeViewRenderer(LessonFigureView);
    },
});
