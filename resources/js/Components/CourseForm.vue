<template>
    <form class="surface-card space-y-6 p-6" @submit.prevent="$emit('submit')">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold">{{ isArabic ? 'تفاصيل الكورس' : 'Course Details' }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ isArabic ? 'عدّل بيانات الكورس العامة وحالة النشر ونوع المحتوى.' : 'Edit the course details, publishing state, and content format.' }}
                </p>
            </div>
            <div class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="statusBadgeClass">
                {{ statusLabel }}
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ t('admin.title') }}</label>
                <input v-model="form.title" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'العنوان الفرعي' : 'Subtitle' }}</label>
                <input v-model="form.subtitle" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.subtitle" class="mt-1 text-sm text-red-600">{{ form.errors.subtitle }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ t('admin.price') }}</label>
                <input v-model="form.price" type="number" step="0.01" min="0" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'العملة' : 'Currency' }}</label>
                <select v-model="form.currency" class="w-full rounded-lg border-slate-300">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                </select>
                <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ t('courses.level') }}</label>
                <select v-model="form.level" class="w-full rounded-lg border-slate-300">
                    <option value="beginner">{{ isArabic ? 'مبتدئ' : 'Beginner' }}</option>
                    <option value="intermediate">{{ isArabic ? 'متوسط' : 'Intermediate' }}</option>
                    <option value="advanced">{{ isArabic ? 'متقدم' : 'Advanced' }}</option>
                </select>
                <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'نوع الكورس' : 'Course Format' }}</label>
                <select v-model="form.course_format" class="w-full rounded-lg border-slate-300">
                    <option value="text">{{ isArabic ? 'كتابي' : 'Text' }}</option>
                    <option value="video">{{ isArabic ? 'مرئي' : 'Video' }}</option>
                    <option value="mixed">{{ isArabic ? 'مختلط' : 'Mixed' }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">
                    {{ isArabic ? 'يوضح للطلاب ما إذا كان الكورس كتابياً أو مرئياً أو يجمع بين الاثنين.' : 'Tell students whether the course is written, video-based, or a mix of both.' }}
                </p>
                <p v-if="form.errors.course_format" class="mt-1 text-sm text-red-600">{{ form.errors.course_format }}</p>
            </div>

            <div>
                <label class="mb-2 block font-bold">{{ isArabic ? 'مدة الكورس بالساعات' : 'Duration Hours' }}</label>
                <input v-model="form.duration_hours" type="number" min="0" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.duration_hours" class="mt-1 text-sm text-red-600">{{ form.errors.duration_hours }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ t('admin.status') }}</label>
                <div class="grid gap-3 sm:grid-cols-3">
                    <label class="rounded-2xl border p-4 transition" :class="form.status === 'draft' ? 'border-slate-900 bg-slate-50' : 'border-slate-200'">
                        <input v-model="form.status" type="radio" value="draft" class="sr-only" />
                        <p class="font-bold">{{ t('admin.draft') }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ isArabic ? 'يظل الكورس مخفياً عن الزوار والطلاب.' : 'Hidden from visitors and students.' }}
                        </p>
                    </label>
                    <label class="rounded-2xl border p-4 transition" :class="form.status === 'published' ? 'border-emerald-600 bg-emerald-50' : 'border-slate-200'">
                        <input v-model="form.status" type="radio" value="published" class="sr-only" />
                        <p class="font-bold">{{ t('admin.published') }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ isArabic ? 'ظاهر في الموقع ويمكن للطلاب المسجلين البدء به.' : 'Visible on the site and ready for enrolled students.' }}
                        </p>
                    </label>
                    <label class="rounded-2xl border p-4 transition" :class="form.status === 'archived' ? 'border-amber-600 bg-amber-50' : 'border-slate-200'">
                        <input v-model="form.status" type="radio" value="archived" class="sr-only" />
                        <p class="font-bold">{{ isArabic ? 'مؤرشف' : 'Archived' }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ isArabic ? 'مخفي من العرض العام مع الاحتفاظ بالمحتوى داخلياً.' : 'Removed from public listings while keeping content intact.' }}
                        </p>
                    </label>
                </div>
                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'وصف صفحة الكورس' : 'Course Landing Content' }}</label>
                <textarea
                    v-model="form.description"
                    rows="8"
                    class="w-full rounded-lg border-slate-300 font-mono text-sm"
                    placeholder="<h2>Overview</h2>&#10;<p>Use safe HTML only.</p>&#10;<pre><code>Code blocks are supported.</code></pre>"
                ></textarea>
                <p class="mt-1 text-xs text-slate-500">
                    {{ isArabic ? 'يُسمح بالعناوين والقوائم والروابط والاقتباسات وكتل الأكواد. سيتم حذف أي HTML غير آمن عند الحفظ.' : 'Headings, lists, links, blockquotes, and code blocks are supported. Unsafe HTML is removed on save.' }}
                </p>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'الوصف المختصر لمحركات البحث' : 'Meta Description' }}</label>
                <input v-model="form.meta_description" class="w-full rounded-lg border-slate-300" />
                <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block font-bold">{{ isArabic ? 'الصورة المصغرة' : 'Thumbnail' }}</label>
                <input
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @input="form.thumbnail = $event.target.files[0]"
                    class="w-full rounded-lg border border-slate-300 p-2"
                />
                <p class="mt-1 text-xs text-slate-500">
                    {{ isArabic ? 'أي صورة يتم رفعها ستُحفظ بصيغة WebP لتقليل الحجم.' : 'Uploaded images are converted to WebP automatically to save space.' }}
                </p>
                <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-red-600">{{ form.errors.thumbnail }}</p>
            </div>
        </div>

        <button class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white transition hover:bg-blue-800" :disabled="form.processing">
            {{ submitLabel || t('admin.save') }}
        </button>
    </form>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const props = defineProps({
    form: { type: Object, required: true },
    submitLabel: { type: String, default: '' },
});

defineEmits(['submit']);

const page = usePage();
const { t } = useTranslations();
const isArabic = computed(() => page.props.locale.current === 'ar');

const statusLabel = computed(() => {
    if (props.form.status === 'published') {
        return t('admin.published');
    }

    if (props.form.status === 'archived') {
        return isArabic.value ? 'مؤرشف' : 'Archived';
    }

    return t('admin.draft');
});

const statusBadgeClass = computed(() => {
    if (props.form.status === 'published') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (props.form.status === 'archived') {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-slate-100 text-slate-700';
});
</script>
