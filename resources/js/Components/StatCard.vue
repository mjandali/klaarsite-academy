<template>
    <div class="surface-card p-5 sm:p-6">
        <p class="mb-2 text-sm text-slate-500">{{ label }}</p>
        <p class="text-3xl font-extrabold text-blue-700 sm:text-4xl">{{ value }}</p>
    </div>
</template>

<script setup>
defineProps({ label: String, value: [String, Number] });
</script>
