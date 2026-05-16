import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

export function useToast() {
    const page = usePage();
    const isArabic = computed(() => page.props.locale?.current === 'ar');

    const baseOptions = () => ({
        rtl: page.props.locale?.dir === 'rtl',
        position: isArabic.value ? toast.POSITION.TOP_LEFT : toast.POSITION.TOP_RIGHT,
        autoClose: 4000,
        closeOnClick: true,
        pauseOnHover: true,
        theme: 'colored',
    });

    return {
        success(message, options = {}) {
            return toast.success(message, { ...baseOptions(), ...options });
        },
        error(message, options = {}) {
            return toast.error(message, { ...baseOptions(), autoClose: 5000, ...options });
        },
        info(message, options = {}) {
            return toast.info(message, { ...baseOptions(), ...options });
        },
        warning(message, options = {}) {
            return toast.warning(message, { ...baseOptions(), autoClose: 5000, ...options });
        },
    };
}
