import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export function useConfirm() {
    const page = usePage();
    const isArabic = computed(() => page.props.locale?.current === 'ar');
    const dir = computed(() => page.props.locale?.dir || 'ltr');

    const fire = (options = {}) => Swal.fire({
        confirmButtonColor: '#1d4ed8',
        cancelButtonColor: '#64748b',
        showCancelButton: true,
        reverseButtons: isArabic.value,
        focusCancel: true,
        confirmButtonText: isArabic.value ? 'تأكيد' : 'Confirm',
        cancelButtonText: isArabic.value ? 'إلغاء' : 'Cancel',
        didOpen: (popup) => {
            popup.setAttribute('dir', dir.value);
        },
        ...options,
    });

    const confirm = async (options = {}) => {
        const result = await fire(options);

        return result.isConfirmed;
    };

    const confirmDestructive = async ({
        title,
        text,
        confirmButtonText,
        cancelButtonText,
        ...rest
    } = {}) => confirm({
        icon: 'warning',
        title: title || (isArabic.value ? 'هل أنت متأكد؟' : 'Are you sure?'),
        text: text || (isArabic.value ? 'لا يمكن التراجع عن هذا الإجراء.' : 'This action cannot be undone.'),
        confirmButtonText: confirmButtonText || (isArabic.value ? 'نعم، تابع' : 'Yes, continue'),
        cancelButtonText: cancelButtonText || (isArabic.value ? 'إلغاء' : 'Cancel'),
        ...rest,
    });

    const alert = (options = {}) => Swal.fire({
        confirmButtonColor: '#1d4ed8',
        confirmButtonText: isArabic.value ? 'حسناً' : 'OK',
        didOpen: (popup) => {
            popup.setAttribute('dir', dir.value);
        },
        ...options,
    });

    return {
        alert,
        confirm,
        confirmDestructive,
    };
}
