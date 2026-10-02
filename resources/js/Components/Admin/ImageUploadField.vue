<script setup>
import { onUnmounted, ref, watch } from 'vue';
import { X } from '@lucide/vue';

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    hint: { type: String, default: 'JPG, PNG, atau WebP.' },
    accept: { type: String, default: 'image/jpeg,image/png,image/webp' },
    required: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
    modelValue: { type: File, default: null },
    previewUrl: { type: String, default: '' },
    previewClass: { type: String, default: '' },
    error: { type: String, default: '' },
    alt: { type: String, default: 'Pratinjau gambar' },
});

const emit = defineEmits(['update:modelValue']);
const localPreviewUrl = ref('');
const fileInput = ref(null);
let objectUrl;

watch(() => props.modelValue, (file) => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = undefined;
    }

    localPreviewUrl.value = '';

    if (file) {
        objectUrl = URL.createObjectURL(file);
        localPreviewUrl.value = objectUrl;
    }
}, { immediate: true });

const updateFile = (event) => {
    emit('update:modelValue', event.target.files?.[0] ?? null);
};

const clearFile = () => {
    if (fileInput.value) {
        fileInput.value.value = '';
    }

    emit('update:modelValue', null);
};

onUnmounted(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }
});
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <label class="admin-label mb-0" :for="id">{{ label }}</label>
            <span class="text-[10px] text-[#55514b]">{{ hint }}</span>
        </div>
        <div class="grid gap-4" :class="compact ? 'grid-cols-1' : 'sm:grid-cols-[minmax(0,1fr)_minmax(180px,0.65fr)]'">
            <input
                :id="id"
                ref="fileInput"
                type="file"
                :accept="accept"
                :required="required"
                :aria-invalid="Boolean(error)"
                :aria-describedby="error ? `${id}-error` : `${id}-hint`"
                class="admin-input file:mr-3 file:border-0 file:bg-[#eee9e0] file:px-3 file:py-2 file:text-[9px] file:font-semibold file:uppercase file:tracking-[0.1em]"
                @change="updateFile"
            >
            <div class="relative grid place-items-center overflow-hidden border border-[#d8d1c6] bg-[#eee9e0]" :class="[compact ? 'aspect-[4/5] max-h-[22rem]' : 'min-h-36', previewClass]">
                <img v-if="localPreviewUrl || previewUrl" :src="localPreviewUrl || previewUrl" :alt="alt" class="h-full w-full" :class="compact ? 'object-cover' : 'max-h-64 object-contain'">
                <span v-else class="px-4 text-center text-[10px] font-medium uppercase tracking-[0.12em] text-[#55514b]">DATA BELUM TERSEDIA</span>
                <button
                    v-if="modelValue"
                    type="button"
                    class="focus-ring absolute right-2 top-2 grid size-9 place-items-center bg-[#f5f2ec] text-[#8b4b45]"
                    :aria-label="`Batalkan pilihan ${label.toLowerCase()}`"
                    @click="clearFile"
                >
                    <X :size="15" />
                </button>
            </div>
        </div>
        <p :id="`${id}-hint`" class="sr-only">{{ hint }}</p>
        <p v-if="error" :id="`${id}-error`" role="alert" class="text-xs text-[#8b4b45]">{{ error }}</p>
    </div>
</template>
