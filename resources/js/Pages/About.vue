<template>
    <PublicLayout>
        <Head :title="t('nav.about')" />

        <PageShell :title="t('nav.about')">
            <p class="text-slate-700 leading-8" v-if="$page.props.locale.current === 'ar'">
                Klaarsite Academy منصة تعليمية عملية تهدف إلى تعليم البرمجة من خلال مشاريع حقيقية، مع تركيز على الفهم، التنظيم، وبناء مهارات قابلة للاستخدام في العمل.
            </p>
            <p class="text-slate-700 leading-8" v-else>
                Klaarsite Academy is a practical learning platform focused on teaching programming through real projects, clean structure, and work-ready skills.
            </p>
        </PageShell>
    </PublicLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PageShell from '@/Components/PageShell.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
</script>
