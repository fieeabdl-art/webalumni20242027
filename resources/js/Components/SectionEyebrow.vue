<script setup>
defineProps({
    number: { type: String, default: '' },
    label: { type: String, required: true },
    light: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center gap-3 text-[9px] font-semibold uppercase tracking-[0.2em]" :class="light ? 'text-white/55' : 'text-[#6b6b65]'">
        <span v-if="number" class="font-editorial text-sm text-[#a38b68]">{{ number }}</span>
        <span class="h-px w-7" :class="light ? 'bg-white/30' : 'bg-[#a38b68]/70'"></span>
        <span>{{ label }}</span>
    </div>
</template>