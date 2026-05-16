<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = page.props.auth.user;
const isArabic = computed(() => page.props.locale.current === 'ar');
const toaster = useToast();

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toaster.success(isArabic.value ? 'تم تحديث بيانات الملف الشخصي.' : 'Profile information updated.');
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-extrabold text-slate-900">{{ isArabic ? 'بيانات الحساب' : 'Profile Information' }}</h2>

            <p class="mt-2 text-sm leading-7 text-slate-600">
                {{ isArabic ? 'حدّث الاسم والبريد الإلكتروني المستخدمين في حسابك.' : 'Update the name and email address used for your account.' }}
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="submit">
            <div>
                <InputLabel for="name" :value="isArabic ? 'الاسم' : 'Name'" />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="isArabic ? 'البريد الإلكتروني' : 'Email'" />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="props.mustVerifyEmail && user.email_verified_at === null" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <p>
                    {{ isArabic ? 'بريدك الإلكتروني غير موثّق بعد.' : 'Your email address is still unverified.' }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-semibold underline underline-offset-2"
                    >
                        {{ isArabic ? 'أعد إرسال رابط التوثيق' : 'Resend verification link' }}
                    </Link>
                </p>

                <p v-if="props.status === 'verification-link-sent'" class="mt-2 font-semibold text-emerald-700">
                    {{ isArabic ? 'تم إرسال رابط توثيق جديد إلى بريدك الإلكتروني.' : 'A new verification link has been sent to your email address.' }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ isArabic ? 'حفظ البيانات' : 'Save Changes' }}</PrimaryButton>
            </div>
        </form>
    </section>
</template>
