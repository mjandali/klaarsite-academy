<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-4 py-8" :dir="$page.props.locale?.dir || 'rtl'">
        <div class="w-full max-w-md text-center">
            <Link href="/" class="text-2xl font-extrabold text-blue-700">Klaarsite Academy</Link>
        </div>

        <div class="mt-6 w-full max-w-md overflow-hidden rounded-2xl bg-white px-6 py-5 shadow-md sm:px-8 sm:py-6">
            <slot />
        </div>
    </div>
</template>
