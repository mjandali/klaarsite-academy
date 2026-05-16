import { usePage } from '@inertiajs/vue3';

export function useTranslations() {
    const page = usePage();

    const t = (key, fallback = null) => {
        const parts = key.split('.');
        let value = page.props.translations;
        for (const part of parts) {
            if (!value || typeof value !== 'object' || !(part in value)) {
                return fallback ?? key;
            }
            value = value[part];
        }
        return value ?? fallback ?? key;
    };

    const isRtl = () => page.props.locale?.dir === 'rtl';

    return { t, isRtl };
}
