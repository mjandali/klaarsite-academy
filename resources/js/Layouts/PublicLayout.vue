<template>
    <div class="min-h-screen bg-slate-50 text-slate-900" :dir="$page.props.locale.dir">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <nav class="page-container py-4">
                <div class="flex items-center justify-between gap-4">
                    <Link href="/" class="text-xl font-extrabold tracking-tight text-blue-700 sm:text-2xl">
                        Klaarsite Academy
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:border-blue-300 hover:text-blue-700 md:hidden"
                        :aria-expanded="isMenuOpen"
                        aria-label="Toggle navigation"
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
                        <Link href="/courses" class="text-slate-700 transition hover:text-blue-700">{{ t('nav.courses') }}</Link>
                        <Link href="/about" class="text-slate-700 transition hover:text-blue-700">{{ t('nav.about') }}</Link>
                        <Link href="/contact" class="text-slate-700 transition hover:text-blue-700">{{ t('nav.contact') }}</Link>
                        <Link
                            v-if="$page.props.auth.user?.role === 'admin'"
                            href="/admin"
                            class="font-bold text-blue-700 transition hover:text-blue-800"
                        >
                            {{ t('nav.admin') }}
                        </Link>
                        <Link
                            v-else-if="$page.props.auth.user"
                            href="/dashboard"
                            class="font-bold text-blue-700 transition hover:text-blue-800"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                        <Link
                            v-if="$page.props.auth.user?.role === 'student'"
                            href="/dashboard/my-courses"
                            class="text-slate-700 transition hover:text-blue-700"
                        >
                            {{ t('nav.my_courses') }}
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard/profile"
                            class="text-slate-700 transition hover:text-blue-700"
                        >
                            {{ t('nav.profile') }}
                        </Link>
                        <Link v-else href="/login" class="font-bold text-blue-700 transition hover:text-blue-800">
                            {{ t('nav.login') }}
                        </Link>
                        <Link
                            :href="languageUrl"
                            class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 transition hover:border-blue-300 hover:text-blue-700"
                        >
                            {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                        </Link>
                    </div>
                </div>

                <div v-show="isMenuOpen" class="mt-4 grid gap-2 border-t border-slate-200 pt-4 md:hidden">
                    <Link href="/courses" class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.courses') }}
                    </Link>
                    <Link href="/about" class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.about') }}
                    </Link>
                    <Link href="/contact" class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" @click="isMenuOpen = false">
                        {{ t('nav.contact') }}
                    </Link>
                    <Link
                        v-if="$page.props.auth.user?.role === 'admin'"
                        href="/admin"
                        class="rounded-xl px-3 py-2 font-bold text-blue-700 transition hover:bg-blue-50 hover:text-blue-800"
                        @click="isMenuOpen = false"
                    >
                        {{ t('nav.admin') }}
                    </Link>
                    <Link
                        v-else-if="$page.props.auth.user"
                        href="/dashboard"
                        class="rounded-xl px-3 py-2 font-bold text-blue-700 transition hover:bg-blue-50 hover:text-blue-800"
                        @click="isMenuOpen = false"
                    >
                        {{ t('nav.dashboard') }}
                    </Link>
                    <Link
                        v-if="$page.props.auth.user?.role === 'student'"
                        href="/dashboard/my-courses"
                        class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                        @click="isMenuOpen = false"
                    >
                        {{ t('nav.my_courses') }}
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard/profile"
                        class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                        @click="isMenuOpen = false"
                    >
                        {{ t('nav.profile') }}
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="rounded-xl px-3 py-2 font-bold text-blue-700 transition hover:bg-blue-50 hover:text-blue-800"
                        @click="isMenuOpen = false"
                    >
                        {{ t('nav.login') }}
                    </Link>
                    <Link
                        :href="languageUrl"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-slate-600 transition hover:border-blue-300 hover:text-blue-700"
                        @click="isMenuOpen = false"
                    >
                        {{ $page.props.locale.current === 'ar' ? 'English' : 'العربية' }}
                    </Link>
                </div>
            </nav>
        </header>

        <AppFlashToaster />

        <main>
            <slot />
        </main>

        <footer class="mt-20 bg-slate-950 text-white">
            <div class="page-container py-10">
                <div class="mb-8 grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <h3 class="mb-4 text-lg font-bold">Klaarsite Academy</h3>
                        <p class="text-sm leading-7 text-slate-400">
                            {{
                                $page.props.locale.current === 'ar'
                                    ? 'أكاديمية عملية لتعليم البرمجة وبناء المشاريع الحقيقية.'
                                    : 'A practical academy for learning programming and building real projects.'
                            }}
                        </p>
                    </div>
                    <div>
                        <h3 class="mb-4 font-bold">{{ t('nav.courses') }}</h3>
                        <Link href="/courses" class="text-sm text-slate-400 transition hover:text-white">{{ t('nav.courses') }}</Link>
                    </div>
                    <div>
                        <h3 class="mb-4 font-bold">{{ t('nav.terms') }}</h3>
                        <ul class="space-y-2 text-sm text-slate-400">
                            <li><Link href="/privacy" class="transition hover:text-white">{{ t('nav.privacy') }}</Link></li>
                            <li><Link href="/terms" class="transition hover:text-white">{{ t('nav.terms') }}</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 font-bold">{{ t('nav.contact') }}</h3>
                        <p class="text-sm text-slate-400">mjandaline@gmail.com</p>
                    </div>
                </div>
                <div class="border-t border-slate-800 pt-8 text-center text-sm text-slate-400">
                    <p>&copy; 2026 Klaarsite Academy. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';
import AppFlashToaster from '@/Components/AppFlashToaster.vue';

const page = usePage();
const isMenuOpen = ref(false);
const { t } = useTranslations();
const languageUrl = computed(() => `/language/${page.props.locale.current === 'ar' ? 'en' : 'ar'}`);
</script>
