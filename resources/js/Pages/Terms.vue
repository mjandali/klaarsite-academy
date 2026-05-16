<template><PublicLayout><Head :title="t('nav.terms')" /><PageShell :title="t('nav.terms')"><p>{{ $page.props.locale.current === 'ar' ? 'هذه صفحة شروط أولية للـ MVP. حدّثها قبل الإطلاق القانوني الفعلي.' : 'This is a starter terms page for the MVP. Update it before production launch.' }}</p></PageShell></PublicLayout></template>
<script setup>
import { Head } from '@inertiajs/vue3'; import PublicLayout from '@/Layouts/PublicLayout.vue'; import PageShell from '@/Components/PageShell.vue'; import { useTranslations } from '@/Composables/useTranslations'; const { t } = useTranslations();
</script>
