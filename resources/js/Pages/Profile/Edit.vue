<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const isArabic = computed(() => page.props.locale.current === 'ar');
const layoutComponent = computed(() => page.props.auth.user?.role === 'admin' ? AdminLayout : StudentLayout);
</script>

<template>
    <Head :title="isArabic ? 'الملف الشخصي' : 'Profile'" />

    <component :is="layoutComponent">
        <section class="page-shell">
            <div class="page-container space-y-8">
                <div>
                    <h1 class="page-title mb-2">{{ isArabic ? 'الملف الشخصي' : 'Profile' }}</h1>
                    <p class="page-lead">
                        {{ isArabic ? 'حدّث بيانات الحساب وكلمة المرور وأدر خيارات الأمان من مكان واحد.' : 'Update your account details, password, and security settings in one place.' }}
                    </p>
                </div>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                    <div class="space-y-6">
                        <div class="surface-card p-6 sm:p-8">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>

                        <div class="surface-card p-6 sm:p-8">
                            <UpdatePasswordForm />
                        </div>
                    </div>

                    <div class="surface-card p-6 sm:p-8">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </section>
    </component>
</template>
