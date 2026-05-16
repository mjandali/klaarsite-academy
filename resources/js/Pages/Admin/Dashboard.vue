<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">Dashboard</h1>

                <div class="mb-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="surface-card p-6">
                        <p class="text-sm text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.total_courses }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-green-600">{{ stats.total_students }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold text-emerald-600">${{ stats.total_revenue }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-gray-600">Pending Orders</p>
                        <p class="text-3xl font-bold text-orange-600">{{ stats.pending_orders }}</p>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <h2 class="mb-4 text-2xl font-bold">Quick Actions</h2>
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <Link href="/admin/courses/create" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700">
                            + New Course
                        </Link>
                        <Link href="/admin/students" class="inline-flex items-center justify-center rounded-xl bg-gray-600 px-6 py-3 font-bold text-white transition hover:bg-gray-700">
                            View Students
                        </Link>
                        <Link href="/admin/orders" class="inline-flex items-center justify-center rounded-xl bg-gray-600 px-6 py-3 font-bold text-white transition hover:bg-gray-700">
                            View Orders
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: Object,
});
</script>
