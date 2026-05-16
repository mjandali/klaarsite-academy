<template>
    <div class="min-h-screen bg-slate-50 text-slate-900" :dir="$page.props.locale.dir">
        <header class="border-b border-blue-900/30 bg-blue-800 text-white shadow">
            <nav class="page-container py-4">
                <div class="flex items-center justify-between gap-4">
                    <Link href="/admin" class="text-xl font-bold tracking-tight sm:text-2xl">Klaarsite Academy</Link>

                    <div class="hidden items-center gap-4 text-sm md:flex md:text-base">
                        <Link href="/" class="text-blue-100 transition hover:text-white">{{ t('nav.view_site') }}</Link>
                        <Link :href="languageUrl" class="text-blue-100 transition hover:text-white">
                            {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                        </Link>
                        <span class="text-blue-100">{{ user.name }}</span>
                        <form class="inline" @submit.prevent="logout">
                            <button type="submit" class="text-blue-100 transition hover:text-white">{{ t('nav.logout') }}</button>
                        </form>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-xl border border-blue-600/70 p-2 text-blue-100 transition hover:border-white hover:text-white md:hidden"
                        :aria-expanded="isSidebarOpen"
                        aria-label="Toggle admin navigation"
                        @click="isSidebarOpen = !isSidebarOpen"
                    >
                        <svg v-if="!isSidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-blue-100 md:hidden">
                    <Link href="/" class="transition hover:text-white">{{ t('nav.view_site') }}</Link>
                    <Link :href="languageUrl" class="transition hover:text-white">
                        {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                    </Link>
                    <span class="min-w-0 truncate">{{ user.name }}</span>
                    <form class="inline" @submit.prevent="logout">
                        <button type="submit" class="transition hover:text-white">{{ t('nav.logout') }}</button>
                    </form>
                </div>
            </nav>
        </header>

        <FlashMessages />

        <div class="lg:flex">
            <aside
                :class="isSidebarOpen ? 'block' : 'hidden'"
                class="border-b border-slate-200 bg-white shadow-sm lg:block lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r"
            >
                <nav class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-1 lg:p-6">
                    <Link href="/admin" class="rounded-xl px-4 py-3 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ t('admin.dashboard') }}</Link>
                    <Link href="/admin/courses" class="rounded-xl px-4 py-3 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ t('admin.courses') }}</Link>
                    <Link href="/admin/students" class="rounded-xl px-4 py-3 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ t('admin.students') }}</Link>
                    <Link href="/admin/orders" class="rounded-xl px-4 py-3 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ t('admin.orders') }}</Link>
                    <Link href="/admin/settings" class="rounded-xl px-4 py-3 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ t('admin.settings') }}</Link>
                </nav>
            </aside>

            <main class="min-w-0 flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';
import FlashMessages from '@/Components/FlashMessages.vue';

const page = usePage();
const isSidebarOpen = ref(false);
const user = page.props.auth.user;
const { t } = useTranslations();
const languageUrl = computed(() => `/language/${page.props.locale.current === 'ar' ? 'en' : 'ar'}`);
const logout = () => router.post(route('logout'));
</script>
