<template>
    <Head :title="t('nav.orders')" />

    <StudentLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">{{ t('nav.orders') }}</h1>

                <div class="table-shell">
                    <table class="table-base min-w-[720px]">
                        <thead class="border-b bg-slate-100">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3 text-start">{{ t('common.order_number') }}</th>
                                <th class="whitespace-nowrap px-5 py-3 text-start">{{ t('common.course') }}</th>
                                <th class="whitespace-nowrap px-5 py-3 text-start">{{ t('common.amount') }}</th>
                                <th class="whitespace-nowrap px-5 py-3 text-start">{{ t('admin.status') }}</th>
                                <th class="whitespace-nowrap px-5 py-3 text-start">{{ t('common.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.id" class="border-b border-slate-200">
                                <td class="px-5 py-4 font-mono text-sm">{{ order.order_number }}</td>
                                <td class="px-5 py-4">{{ order.course.title }}</td>
                                <td class="px-5 py-4">{{ order.amount }} {{ order.currency }}</td>
                                <td class="px-5 py-4">{{ order.status }}</td>
                                <td class="whitespace-nowrap px-5 py-4">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!orders.data.length" class="p-8 text-center text-slate-500">{{ t('common.empty') }}</div>
                </div>
            </div>
        </section>
    </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

defineProps({ orders: Object });

const { t } = useTranslations();
</script>
