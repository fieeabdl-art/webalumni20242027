<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';
import GalleryLightbox from '../../Components/GalleryLightbox.vue';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    memories: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const selectedCategory = ref('Semua');
const categoryFilters = computed(() => ['Semua', ...props.categories]);
const filteredMemories = computed(() => selectedCategory.value === 'Semua'
    ? props.memories
    : props.memories.filter((memory) => memory.category === selectedCategory.value));
</script>

<template>
    <Head title="Kenangan Kami"><meta name="description" content="Arsip foto dan kenangan angkatan."></Head>
    <PublicLayout :site="site">
        <section class="px-6 pb-16 pt-36 sm:px-10 sm:pb-24 sm:pt-44 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="04" label="Arsip foto" />
                <div class="mt-8 grid gap-7 lg:grid-cols-[1fr_0.65fr] lg:items-end">
                    <h1 class="font-editorial text-6xl leading-[0.94] sm:text-8xl">KENANGAN<br class="hidden sm:block"> KAMI.</h1>
                    <p class="max-w-md pb-2 text-sm leading-7 text-[#6b6b65]">Satu gambar dapat membawa kita kembali. Jelajahi potongan waktu yang disimpan bersama.</p>
                </div>
                <div class="mt-12 flex flex-wrap gap-x-6 gap-y-3 border-y border-black/15 py-4" role="group" aria-label="Filter kategori kenangan">
                    <button v-for="category in categoryFilters" :key="category" type="button" class="focus-ring py-2 text-[9px] uppercase tracking-[0.14em] transition-colors" :class="selectedCategory === category ? 'text-[#8b4b45]' : 'text-[#6b6b65] hover:text-[#242424]'" :aria-pressed="selectedCategory === category" @click="selectedCategory = category">{{ category }}</button>
                </div>
                <div v-if="filteredMemories.length" class="mt-8"><GalleryLightbox :memories="filteredMemories" /></div>
                <p v-else class="border-b border-black/15 py-8 text-[10px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</p>
            </div>
        </section>
    </PublicLayout>
</template>