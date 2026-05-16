<template>
    <div class="min-h-screen bg-slate-50 text-slate-900" :dir="$page.props.locale.dir">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm">
            <nav class="page-container py-4">
                <div class="flex items-center justify-between gap-4">
                    <Link href="/" class="text-xl font-extrabold tracking-tight text-blue-700 sm:text-2xl">
                        Klaarsite Academy
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:border-blue-300 hover:text-blue-700 md:hidden"
                        :aria-expanded="isMenuOpen"
                        aria-label="Toggle student navigation"
                        @click="isMenuOpen = !isMenuOpen"
                    >
                        <svg v-if="!isMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>

                    <div class="hidden items-center gap-3 text-sm md:flex md:flex-wrap md:justify-end md:gap-5 md:text-base">
                        <Link href="/dashboard" class="font-semibold text-slate-700 transition hover:text-blue-700">{{ t('nav.dashboard') }}</Link>
                        <Link href="/dashboard/my-courses" class="text-slate-700 transition hover:text-blue-700">{{ t('nav.my_courses') }}</Link>
                        <Link href="/courses" class="text-slate-700 transition hover:text-blue-700">{{ t('nav.browse') }}</Link>
                        <Link :href="languageUrl" class="text-slate-600 transition hover:text-blue-700">
                            {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                        </Link>
                        <span class="max-w-[180px] truncate text-slate-500">{{ user.name }}</span>
                        <form class="inline" @submit.prevent="logout">
                            <button type="submit" class="text-slate-700 transition hover:text-red-600">{{ t('nav.logout') }}</button>
                        </form>
                    </div>
                </div>

                <div v-show="isMenuOpen" class="mt-4 grid gap-2 border-t border-slate-200 pt-4 md:hidden">
                    <Link href="/dashboard" class="rounded-xl px-3 py-2 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.dashboard') }}
                    </Link>
                    <Link href="/dashboard/my-courses" class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.my_courses') }}
                    </Link>
                    <Link href="/courses" class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.browse') }}
                    </Link>
                    <Link
                        :href="languageUrl"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-slate-600 transition hover:border-blue-300 hover:text-blue-700"
                        @click="isMenuOpen = false"
                    >
                        {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                    </Link>
                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-500">{{ user.name }}</div>
                    <form @submit.prevent="logout">
                        <button type="submit" class="w-full rounded-xl px-3 py-2 text-right text-slate-700 transition hover:bg-red-50 hover:text-red-600">
                            {{ t('nav.logout') }}
                        </button>
                    </form>
                </div>
            </nav>
        </header>

        <FlashMessages />

        <main>
            <slot />
        </main>

        <footer class="mt-20 bg-slate-950 text-white">
            <div class="page-container py-8 text-center text-sm text-slate-400">
                <p>&copy; 2026 Klaarsite Academy. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';
import FlashMessages from '@/Components/FlashMessages.vue';

const page = usePage();
const isMenuOpen = ref(false);
const user = page.props.auth.user;
const { t } = useTranslations();
const languageUrl = computed(() => `/language/${page.props.locale.current === 'ar' ? 'en' : 'ar'}`);
const logout = () => router.post(route('logout'));
</script>
