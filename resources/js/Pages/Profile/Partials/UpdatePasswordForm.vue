<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const isArabic = computed(() => page.props.locale.current === 'ar');
const toaster = useToast();
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toaster.success(isArabic.value ? 'تم تحديث كلمة المرور.' : 'Password updated successfully.');
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-extrabold text-slate-900">{{ isArabic ? 'تحديث كلمة المرور' : 'Update Password' }}</h2>

            <p class="mt-2 text-sm leading-7 text-slate-600">
                {{ isArabic ? 'استخدم كلمة مرور قوية وطويلة للحفاظ على أمان حسابك.' : 'Use a strong, long password to keep your account secure.' }}
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
            <div>
                <InputLabel for="current_password" :value="isArabic ? 'كلمة المرور الحالية' : 'Current Password'" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" :value="isArabic ? 'كلمة المرور الجديدة' : 'New Password'" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" :value="isArabic ? 'تأكيد كلمة المرور' : 'Confirm Password'" />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ isArabic ? 'حفظ كلمة المرور' : 'Save Password' }}</PrimaryButton>
            </div>
        </form>
    </section>
</template>
