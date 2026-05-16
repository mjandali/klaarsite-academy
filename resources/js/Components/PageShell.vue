<template>
    <section class="page-shell">
        <div class="page-container-narrow">
            <div class="surface-card p-6 sm:p-8">
                <h1 class="page-title mb-5">{{ title }}</h1>
                <div class="prose-content text-slate-700">
                    <slot />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({ title: String });
</script>
