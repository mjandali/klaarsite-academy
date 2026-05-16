<template>
    <Head title="Settings" />

    <AdminLayout>
        <section class="page-shell">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <h1 class="page-title mb-8">Settings</h1>

                <div class="surface-card p-6 sm:p-8">
                    <h2 class="mb-6 text-2xl font-bold">Site Settings</h2>

                    <form class="space-y-6">
                        <div>
                            <label class="mb-2 block font-bold">Site Name</label>
                            <input type="text" value="Klaarsite Academy" class="w-full rounded-xl border-gray-300 px-4 py-3" />
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">Contact Email</label>
                            <input type="email" value="mjandaline@gmail.com" class="w-full rounded-xl border-gray-300 px-4 py-3" />
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">Default Currency</label>
                            <select class="w-full rounded-xl border-gray-300 px-4 py-3">
                                <option>USD</option>
                                <option>EUR</option>
                                <option>GBP</option>
                            </select>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700 sm:w-auto">
                            Save Settings
                        </button>
                    </form>

                    <hr class="my-8">

                    <h2 class="mb-6 text-2xl font-bold">Payment Settings</h2>
                    <p class="mb-4 text-gray-600">Configure payment providers</p>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-gray-300 p-4">
                            <h3 class="font-bold">Stripe Configuration</h3>
                            <p class="mt-2 text-sm text-gray-600">Add your Stripe API keys in Phase 4</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
