<template><PublicLayout><Head :title="t('nav.privacy')" /><PageShell :title="t('nav.privacy')"><p>{{ $page.props.locale.current === 'ar' ? 'هذه صفحة خصوصية أولية للـ MVP. حدّثها قبل الإطلاق القانوني الفعلي.' : 'This is a starter privacy page for the MVP. Update it before production launch.' }}</p></PageShell></PublicLayout></template>
<script setup>
import { Head } from '@inertiajs/vue3'; import PublicLayout from '@/Layouts/PublicLayout.vue'; import PageShell from '@/Components/PageShell.vue'; import { useTranslations } from '@/Composables/useTranslations'; const { t } = useTranslations();
</script>
