<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const toaster = useToast();

let lastSuccess = null;
let lastError = null;

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success && flash.success !== lastSuccess) {
            lastSuccess = flash.success;
            toaster.success(flash.success);
        }

        if (flash?.error && flash.error !== lastError) {
            lastError = flash.error;
            toaster.error(flash.error);
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <span class="sr-only" data-toast-flash="true">flash-toasts</span>
</template>
