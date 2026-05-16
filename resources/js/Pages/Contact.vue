<template>
    <PublicLayout>
        <Head :title="t('nav.contact')" />

        <section class="page-shell">
            <div class="page-container-narrow">
                <div class="surface-card p-6 sm:p-8">
                    <h1 class="page-title mb-6">{{ t('nav.contact') }}</h1>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label class="mb-2 block font-bold">{{ t('common.name') }}</label>
                            <input v-model="form.name" class="w-full rounded-xl border-slate-300" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ t('common.email') }}</label>
                            <input v-model="form.email" type="email" class="w-full rounded-xl border-slate-300" />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">{{ t('common.message') }}</label>
                            <textarea v-model="form.message" rows="6" class="w-full rounded-xl border-slate-300"></textarea>
                            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>

                        <button
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-6 py-3 font-bold text-white transition hover:bg-blue-800 sm:w-auto"
                            :disabled="form.processing"
                        >
                            {{ t('common.send') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
const form = useForm({ name: '', email: '', message: '' });
const submit = () => form.post('/contact', { onSuccess: () => form.reset() });
</script>
