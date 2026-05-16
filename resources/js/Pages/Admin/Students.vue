<template>
    <Head title="Students" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container">
                <h1 class="page-title mb-8">Students</h1>

                <div class="table-shell">
                    <table class="table-base">
                        <thead class="border-b bg-gray-100">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Name</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Email</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Courses</th>
                                <th class="whitespace-nowrap px-6 py-3 text-left">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="student in students.data" :key="student.id" class="border-b border-slate-200 hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold">{{ student.name }}</td>
                                <td class="px-6 py-4">{{ student.email }}</td>
                                <td class="px-6 py-4">{{ student.enrollments.length }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ new Date(student.created_at).toLocaleDateString() }}</td>
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
    students: Object,
});
</script>
