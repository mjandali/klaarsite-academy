import { Extension } from '@tiptap/core';

const TEXT_BLOCK_TYPES = ['paragraph', 'heading', 'blockquote', 'listItem'];
const ALLOWED_DIRECTIONS = ['rtl', 'ltr', 'auto'];
const ALLOWED_ALIGNMENTS = ['start', 'center', 'end', 'left', 'right'];

const classForAlignment = (value) => value ? `text-align-${value}` : null;

const alignmentFromClass = (value) => {
    const match = String(value || '').match(/(?:^|\s)text-align-(start|center|end|left|right)(?:\s|$)/);

    return match?.[1] || null;
};

const applyToSelectedTextBlocks = (state, dispatch, attrs = {}) => {
    const { from, to } = state.selection;
    const tr = state.tr;
    let changed = false;

    state.doc.nodesBetween(from, to, (node, pos) => {
        if (!TEXT_BLOCK_TYPES.includes(node.type.name)) {
            return true;
        }

        const nextAttrs = { ...node.attrs };

        Object.entries(attrs).forEach(([key, value]) => {
            nextAttrs[key] = value || null;
        });

        tr.setNodeMarkup(pos, undefined, nextAttrs, node.marks);
        changed = true;

        return true;
    });

    if (!changed) {
        const $from = state.selection.$from;

        for (let depth = $from.depth; depth > 0; depth -= 1) {
            const node = $from.node(depth);

            if (TEXT_BLOCK_TYPES.includes(node.type.name)) {
                const pos = $from.before(depth);
                const nextAttrs = { ...node.attrs };

                Object.entries(attrs).forEach(([key, value]) => {
                    nextAttrs[key] = value || null;
                });

                tr.setNodeMarkup(pos, undefined, nextAttrs, node.marks);
                changed = true;
                break;
            }
        }
    }

    if (changed && dispatch) {
        dispatch(tr);
    }

    return changed;
};

export const TextBlockAttributes = Extension.create({
    name: 'textBlockAttributes',

    addGlobalAttributes() {
        return [
            {
                types: TEXT_BLOCK_TYPES,
                attributes: {
                    dir: {
                        default: null,
                        parseHTML: (element) => {
                            const value = element.getAttribute('dir');

                            return ALLOWED_DIRECTIONS.includes(value) ? value : null;
                        },
                        renderHTML: (attributes) => {
                            if (!ALLOWED_DIRECTIONS.includes(attributes.dir)) {
                                return {};
                            }

                            return { dir: attributes.dir };
                        },
                    },
                    textAlign: {
                        default: null,
                        parseHTML: (element) => alignmentFromClass(element.getAttribute('class')),
                        renderHTML: (attributes) => {
                            if (!ALLOWED_ALIGNMENTS.includes(attributes.textAlign)) {
                                return {};
                            }

                            return { class: classForAlignment(attributes.textAlign) };
                        },
                    },
                },
            },
        ];
    },

    addCommands() {
        return {
            setTextDirection: (direction) => ({ state, dispatch }) => {
                const value = ALLOWED_DIRECTIONS.includes(direction) ? direction : null;

                return applyToSelectedTextBlocks(state, dispatch, { dir: value });
            },
            unsetTextDirection: () => ({ state, dispatch }) => applyToSelectedTextBlocks(state, dispatch, { dir: null }),
            setTextAlignment: (alignment) => ({ state, dispatch }) => {
                const value = ALLOWED_ALIGNMENTS.includes(alignment) ? alignment : null;

                return applyToSelectedTextBlocks(state, dispatch, { textAlign: value });
            },
            unsetTextAlignment: () => ({ state, dispatch }) => applyToSelectedTextBlocks(state, dispatch, { textAlign: null }),
        };
    },
});
