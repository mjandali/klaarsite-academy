<script setup>
import { computed, nextTick, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const page = usePage();
const isArabic = computed(() => page.props.locale.current === 'ar');
const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-xl font-extrabold text-slate-900">{{ isArabic ? 'حذف الحساب' : 'Delete Account' }}</h2>

            <p class="mt-2 text-sm leading-7 text-slate-600">
                {{
                    isArabic
                        ? 'عند حذف الحساب سيتم حذف بياناته وموارده نهائياً. تأكد من الاحتفاظ بأي بيانات تحتاجها قبل المتابعة.'
                        : 'Deleting your account will permanently remove its data and resources. Make sure you keep anything you need before continuing.'
                }}
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">{{ isArabic ? 'حذف الحساب نهائياً' : 'Delete Account Permanently' }}</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900">
                    {{ isArabic ? 'هل أنت متأكد من حذف حسابك؟' : 'Are you sure you want to delete your account?' }}
                </h2>

                <p class="mt-2 text-sm leading-7 text-slate-600">
                    {{
                        isArabic
                            ? 'هذا الإجراء نهائي. أدخل كلمة المرور لتأكيد حذف الحساب بشكل كامل.'
                            : 'This action is permanent. Enter your password to confirm deleting the account completely.'
                    }}
                </p>

                <div class="mt-6">
                    <InputLabel for="password" :value="isArabic ? 'كلمة المرور' : 'Password'" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        :placeholder="isArabic ? 'كلمة المرور' : 'Password'"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">{{ isArabic ? 'إلغاء' : 'Cancel' }}</SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ isArabic ? 'تأكيد حذف الحساب' : 'Confirm Account Deletion' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
