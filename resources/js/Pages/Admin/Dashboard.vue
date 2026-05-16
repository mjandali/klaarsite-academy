<template>
    <Head :title="isArabic ? 'لوحة الإدارة' : 'Admin Dashboard'" />

    <AdminLayout>
        <section class="page-shell">
            <div class="page-container space-y-8">
                <div>
                    <h1 class="page-title mb-2">{{ isArabic ? 'لوحة الإدارة' : 'Admin Dashboard' }}</h1>
                    <p class="page-lead">
                        {{ isArabic ? 'نظرة سريعة على الأداء العام، والتحليلات اليومية، ومسار التحويل من مشاهدة الكورس حتى إتمام الشراء.' : 'A quick view of platform health, daily analytics, and the conversion path from course views to completed purchases.' }}
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="surface-card p-6">
                        <p class="text-sm text-slate-500">{{ isArabic ? 'إجمالي الكورسات' : 'Total Courses' }}</p>
                        <p class="mt-2 text-3xl font-extrabold text-blue-700">{{ stats.total_courses }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-slate-500">{{ isArabic ? 'إجمالي الطلاب' : 'Total Students' }}</p>
                        <p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ stats.total_students }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-slate-500">{{ isArabic ? 'إجمالي الإيرادات' : 'Total Revenue' }}</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-900">${{ stats.total_revenue }}</p>
                    </div>
                    <div class="surface-card p-6">
                        <p class="text-sm text-slate-500">{{ isArabic ? 'الطلبات المعلقة' : 'Pending Orders' }}</p>
                        <p class="mt-2 text-3xl font-extrabold text-amber-600">{{ stats.pending_orders }}</p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-3">
                    <article v-for="period in analytics.periods" :key="period.key" class="surface-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-extrabold">{{ periodLabel(period.key) }}</h2>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-600">
                                {{ period.total }} {{ isArabic ? 'إجمالي الأحداث' : 'events' }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ isArabic ? 'زيارات الصفحات' : 'Page Views' }}</p>
                                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ period.page_views }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ isArabic ? 'مشاهدات الكورسات' : 'Course Views' }}</p>
                                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ period.course_views }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ isArabic ? 'مشاهدات الدروس' : 'Lesson Views' }}</p>
                                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ period.lesson_views }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ isArabic ? 'بدايات الدفع' : 'Checkout Starts' }}</p>
                                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ period.checkout_starts }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ isArabic ? 'المشتريات المكتملة' : 'Completed Purchases' }}</p>
                            <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ period.completed_purchases }}</p>
                        </div>
                    </article>
                </div>

                <div class="surface-card p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-extrabold">{{ isArabic ? 'مسار التحويل خلال 30 يوماً' : '30-Day Conversion Funnel' }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ isArabic ? 'من مشاهدة صفحة الكورس إلى بدء الدفع ثم الشراء المكتمل.' : 'From course page views to checkout starts and completed purchases.' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-blue-50 p-5">
                            <p class="text-sm font-semibold text-blue-700">{{ isArabic ? 'مشاهدات الكورسات' : 'Course Views' }}</p>
                            <p class="mt-3 text-3xl font-extrabold text-blue-900">{{ analytics.funnel.course_views }}</p>
                        </div>
                        <div class="rounded-2xl bg-amber-50 p-5">
                            <p class="text-sm font-semibold text-amber-700">{{ isArabic ? 'بدايات الدفع' : 'Checkout Starts' }}</p>
                            <p class="mt-3 text-3xl font-extrabold text-amber-900">{{ analytics.funnel.checkout_starts }}</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-5">
                            <p class="text-sm font-semibold text-emerald-700">{{ isArabic ? 'المشتريات المكتملة' : 'Completed Purchases' }}</p>
                            <p class="mt-3 text-3xl font-extrabold text-emerald-900">{{ analytics.funnel.completed_purchases }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-3">
                    <div class="surface-card p-6">
                        <h2 class="text-xl font-extrabold">{{ isArabic ? 'أكثر الكورسات مشاهدة' : 'Top Courses by Views' }}</h2>
                        <div v-if="analytics.topCourses.length" class="mt-5 space-y-3">
                            <div v-for="course in analytics.topCourses" :key="course.course_id" class="rounded-2xl border border-slate-200 px-4 py-3">
                                <p class="font-bold text-slate-900">{{ course.title || (isArabic ? 'كورس محذوف' : 'Deleted Course') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ course.total }} {{ isArabic ? 'مشاهدة' : 'views' }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-5 text-sm text-slate-500">{{ isArabic ? 'لا توجد بيانات كافية بعد.' : 'No course view data yet.' }}</p>
                    </div>

                    <div class="surface-card p-6">
                        <h2 class="text-xl font-extrabold">{{ isArabic ? 'أهم مصادر الزيارة' : 'Top Referrers' }}</h2>
                        <div v-if="analytics.topReferrers.length" class="mt-5 space-y-3">
                            <div v-for="referrer in analytics.topReferrers" :key="referrer.referrer" class="rounded-2xl border border-slate-200 px-4 py-3">
                                <p class="truncate font-bold text-slate-900">{{ referrer.referrer }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ referrer.total }} {{ isArabic ? 'زيارة' : 'hits' }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-5 text-sm text-slate-500">{{ isArabic ? 'لا توجد إحالات محفوظة بعد.' : 'No referrer data yet.' }}</p>
                    </div>

                    <div class="surface-card p-6">
                        <h2 class="text-xl font-extrabold">{{ isArabic ? 'حملات UTM' : 'UTM Campaigns' }}</h2>
                        <div v-if="analytics.topCampaigns.length" class="mt-5 space-y-3">
                            <div v-for="campaign in analytics.topCampaigns" :key="`${campaign.utm_campaign}-${campaign.utm_source}-${campaign.utm_medium}`" class="rounded-2xl border border-slate-200 px-4 py-3">
                                <p class="font-bold text-slate-900">{{ campaign.utm_campaign }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ campaign.utm_source || '-' }} / {{ campaign.utm_medium || '-' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ campaign.total }} {{ isArabic ? 'حدثاً' : 'events' }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-5 text-sm text-slate-500">{{ isArabic ? 'لا توجد حملات مسجلة بعد.' : 'No UTM campaigns recorded yet.' }}</p>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <h2 class="mb-4 text-2xl font-extrabold">{{ isArabic ? 'إجراءات سريعة' : 'Quick Actions' }}</h2>
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <Link href="/admin/courses/create" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700">
                            {{ isArabic ? '+ كورس جديد' : '+ New Course' }}
                        </Link>
                        <Link href="/admin/students" class="inline-flex items-center justify-center rounded-xl bg-slate-700 px-6 py-3 font-bold text-white transition hover:bg-slate-800">
                            {{ isArabic ? 'عرض الطلاب' : 'View Students' }}
                        </Link>
                        <Link href="/admin/orders" class="inline-flex items-center justify-center rounded-xl bg-slate-700 px-6 py-3 font-bold text-white transition hover:bg-slate-800">
                            {{ isArabic ? 'عرض الطلبات' : 'View Orders' }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: { type: Object, required: true },
    analytics: { type: Object, required: true },
});

const page = usePage();
const isArabic = computed(() => page.props.locale.current === 'ar');

const periodLabel = (key) => {
    if (key === 'today') {
        return isArabic.value ? 'اليوم' : 'Today';
    }

    if (key === '7d') {
        return isArabic.value ? 'آخر 7 أيام' : 'Last 7 Days';
    }

    return isArabic.value ? 'آخر 30 يوماً' : 'Last 30 Days';
};
</script>
