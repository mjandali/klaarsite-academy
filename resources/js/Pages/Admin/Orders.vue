<template>
    <Head title="Orders" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">Orders</h1>

                <div class="table-shell">
                    <table class="table-base min-w-[760px]">
                        <thead class="border-b bg-gray-100">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Order #</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Student</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Course</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Amount</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Status</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.id" class="border-b border-slate-200 hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm">{{ order.order_number }}</td>
                                <td class="px-6 py-4">{{ order.user.name }}</td>
                                <td class="px-6 py-4">{{ order.course.title }}</td>
                                <td class="px-6 py-4 font-bold">${{ order.amount }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="order.status === 'completed'" class="rounded-full bg-green-100 px-2.5 py-1 text-sm text-green-800">Completed</span>
                                    <span v-else-if="order.status === 'pending'" class="rounded-full bg-yellow-100 px-2.5 py-1 text-sm text-yellow-800">Pending</span>
                                    <span v-else class="rounded-full bg-red-100 px-2.5 py-1 text-sm text-red-800">{{ order.status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    orders: Object,
});
</script>
