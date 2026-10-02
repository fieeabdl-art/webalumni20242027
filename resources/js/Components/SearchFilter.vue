<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    classValue: { type: String, default: '' },
    majorValue: { type: String, default: '' },
    classes: { type: Array, default: () => [] },
    majors: { type: Array, default: () => [] },
});

defineEmits(['update:modelValue', 'update:classValue', 'update:majorValue']);
</script>

<template>
    <div class="grid gap-3 border-y border-black/15 py-4 sm:grid-cols-[minmax(220px,1fr)_minmax(150px,0.35fr)_minmax(150px,0.35fr)]">
        <label class="flex min-h-11 items-center border-b border-black/20 sm:border-b-0 sm:border-r sm:pr-4">
            <span class="sr-only">Cari nama anggota</span>
            <input :value="modelValue" type="search" placeholder="Cari nama anggota" class="focus-ring w-full bg-transparent px-1 py-2 text-xs placeholder:text-[#6b6b65]" @input="$emit('update:modelValue', $event.target.value)">
        </label>
        <label class="flex min-h-11 items-center border-b border-black/20 sm:border-b-0 sm:border-r sm:px-3">
            <span class="sr-only">Filter kelas</span>
            <select :value="classValue" class="focus-ring w-full bg-transparent px-1 py-2 text-[10px] uppercase tracking-[0.1em]" @change="$emit('update:classValue', $event.target.value)">
                <option value="">Semua kelas</option>
                <option v-for="item in classes" :key="item" :value="item">{{ item }}</option>
            </select>
        </label>
        <label class="flex min-h-11 items-center sm:px-3">
            <span class="sr-only">Filter jurusan</span>
            <select :value="majorValue" class="focus-ring w-full bg-transparent px-1 py-2 text-[10px] uppercase tracking-[0.1em]" @change="$emit('update:majorValue', $event.target.value)">
                <option value="">Semua jurusan</option>
                <option v-for="item in majors" :key="item" :value="item">{{ item }}</option>
            </select>
        </label>
    </div>
</template>