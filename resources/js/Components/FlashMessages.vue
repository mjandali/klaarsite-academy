<template>
    <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="container mx-auto px-4 mt-4">
        <div v-if="$page.props.flash.success" class="rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash.error" class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            {{ $page.props.flash.error }}
        </div>
    </div>
</template>
