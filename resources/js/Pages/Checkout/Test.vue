<template>
    <StudentLayout>
        <Head :title="t('checkout.title')" />
        <div class="py-12">
            <div class="container mx-auto px-4 max-w-2xl">
                <div class="bg-white border rounded-2xl shadow-sm p-8 text-center">
                    <h1 class="text-3xl font-extrabold mb-4">{{ t('checkout.title') }}</h1>
                    <p class="text-slate-600 mb-2">{{ order.course.title }}</p>
                    <p class="text-4xl font-extrabold text-blue-700 mb-8">{{ order.amount }} {{ order.currency }}</p>
                    <p class="bg-yellow-50 text-yellow-800 rounded-xl p-4 mb-6 text-sm">
                        {{ $page.props.locale.current === 'ar' ? 'وضع الدفع التجريبي مفعّل لأن مفاتيح Stripe غير مضبوطة في البيئة الحالية.' : 'Test checkout is active because Stripe keys are not configured in this environment.' }}
                    </p>
                    <form @submit.prevent="form.post(`/checkout/${order.id}/test/complete`)">
                        <button class="bg-blue-700 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-800" :disabled="form.processing">{{ t('checkout.test_pay') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
defineProps({ order: Object });
const { t } = useTranslations();
const form = useForm({});
</script>
