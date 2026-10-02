<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    majorValue: { type: String, default: '' },
    majors: { type: Array, default: () => [] },
    classValue: { type: String, default: '' },
    classes: { type: Array, default: () => [] },
});

defineEmits(['update:modelValue', 'update:majorValue', 'update:classValue']);
</script>

<template>
    <div class="grid gap-3 border-y border-[#d8d1c6] py-4 sm:grid-cols-[minmax(220px,1fr)_repeat(2,minmax(150px,0.4fr))]">
        <label class="flex min-h-11 items-center border-b border-[#d8d1c6] sm:border-b-0 sm:border-r sm:pr-4">
            <span class="sr-only">Cari nama anggota</span>
            <input :value="modelValue" type="search" placeholder="Cari nama, panggilan, atau jurusan" class="focus-ring w-full bg-transparent px-1 py-2 text-sm text-[#171717] placeholder:text-[#55514b]" @input="$emit('update:modelValue', $event.target.value)">
        </label>
        <label class="flex min-h-11 items-center sm:px-3">
            <span class="sr-only">Filter jurusan</span>
            <select :value="majorValue" class="focus-ring w-full bg-transparent px-1 py-2 text-sm text-[#171717]" @change="$emit('update:majorValue', $event.target.value)">
                <option value="">Semua jurusan</option>
                <option v-for="item in majors" :key="item" :value="item">{{ item.toLocaleUpperCase('id') }}</option>
            </select>
        </label>
        <label v-if="classes.length" class="flex min-h-11 items-center border-t border-[#d8d1c6] sm:border-t-0 sm:border-l sm:px-3">
            <span class="sr-only">Filter kelas</span>
            <select :value="classValue" class="focus-ring w-full bg-transparent px-1 py-2 text-sm text-[#171717]" @change="$emit('update:classValue', $event.target.value)">
                <option value="">Semua kelas</option>
                <option v-for="item in classes" :key="item" :value="item">{{ item }}</option>
            </select>
        </label>
    </div>
</template>