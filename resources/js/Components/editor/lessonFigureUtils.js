export const LESSON_FIGURE_LAYOUTS = ['full', 'center', 'start', 'end'];
export const LESSON_FIGURE_WIDTHS = ['25', '33', '50', '66', '100'];
export const LESSON_FIGURE_ALLOWED_CLASSES = [
    'media-block',
    'media-center',
    'media-start',
    'media-end',
    'media-w-25',
    'media-w-33',
    'media-w-50',
    'media-w-66',
    'media-w-100',
    'media-wrap',
];

export const normalizeLessonFigureLayout = (value) => LESSON_FIGURE_LAYOUTS.includes(value) ? value : 'center';

export const normalizeLessonFigureWidth = (value) => {
    const width = String(value || '');

    return LESSON_FIGURE_WIDTHS.includes(width) ? width : '50';
};

export const buildLessonFigureClasses = ({
    layout = 'center',
    width = '50',
    wrap = false,
} = {}) => {
    const normalizedLayout = normalizeLessonFigureLayout(layout);
    const normalizedWidth = normalizedLayout === 'full' ? '100' : normalizeLessonFigureWidth(width);
    const classes = [];

    if (normalizedLayout === 'full') {
        classes.push('media-block', 'media-w-100');
    } else {
        classes.push(`media-${normalizedLayout}`, `media-w-${normalizedWidth}`);
    }

    if (wrap && ['start', 'end'].includes(normalizedLayout)) {
        classes.push('media-wrap');
    }

    return classes.join(' ');
};

export const parseLessonFigureClasses = (className = '') => {
    const tokens = String(className)
        .split(/\s+/)
        .map((token) => token.trim())
        .filter(Boolean);

    let layout = 'center';
    let width = '50';
    let wrap = false;

    if (tokens.includes('media-block')) {
        layout = 'full';
        width = '100';
    } else if (tokens.includes('media-center')) {
        layout = 'center';
    } else if (tokens.includes('media-start')) {
        layout = 'start';
    } else if (tokens.includes('media-end')) {
        layout = 'end';
    }

    for (const candidate of LESSON_FIGURE_WIDTHS) {
        if (tokens.includes(`media-w-${candidate}`)) {
            width = candidate;
            break;
        }
    }

    if (layout === 'full') {
        width = '100';
    }

    wrap = tokens.includes('media-wrap') && ['start', 'end'].includes(layout);

    return {
        layout,
        width,
        wrap,
    };
};

export const normalizeLessonFigureAttrs = (attrs = {}) => ({
    src: typeof attrs.src === 'string' ? attrs.src : '',
    alt: typeof attrs.alt === 'string' ? attrs.alt : '',
    caption: typeof attrs.caption === 'string' ? attrs.caption : '',
    layout: normalizeLessonFigureLayout(attrs.layout),
    width: attrs.layout === 'full' ? '100' : normalizeLessonFigureWidth(attrs.width),
    wrap: Boolean(attrs.wrap) && ['start', 'end'].includes(normalizeLessonFigureLayout(attrs.layout)),
});
