<script setup>
import { onMounted, onUnmounted } from 'vue';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

const props = defineProps({
    memories: { type: Array, default: () => [] },
});

let lightbox;

onMounted(() => {
    lightbox = new PhotoSwipeLightbox({
        gallery: '#memory-archive',
        children: 'a[data-lightbox]',
        pswpModule: () => import('photoswipe'),
        bgOpacity: 0.94,
        wheelToZoom: true,
    });

    lightbox.on('uiRegister', () => {
        lightbox.pswp.ui.registerElement({
            name: 'archive-caption',
            order: 9,
            isButton: false,
            appendTo: 'root',
            html: '',
            onInit: (element, slide) => {
                element.className = 'pswp__archive-caption';
                element.textContent = slide.data.element?.dataset.caption || '';
            },
        });
    });

    lightbox.init();
});

onUnmounted(() => lightbox?.destroy());
</script>

<template>
    <div id="memory-archive" class="columns-1 gap-5 sm:columns-2 lg:columns-3">
        <a
            v-for="(memory, index) in memories"
            :key="memory.id"
            :id="memory.slug"
            data-lightbox
            :href="memory.image_url"
            :data-pswp-width="1600"
            :data-pswp-height="1200"
            :data-caption="[memory.title, memory.category, memory.date].filter(Boolean).join(' · ')"
            class="group mb-5 block break-inside-avoid overflow-hidden bg-[#e4ded3]"
            :aria-label="`Buka foto ${memory.title}`"
        >
            <img :src="memory.image_url" :alt="memory.title" :loading="index < 3 ? 'eager' : 'lazy'" class="w-full transition-transform duration-700 group-hover:scale-[1.035]">
            <div class="flex items-start justify-between gap-4 px-3 py-3">
                <div><p class="font-editorial text-lg">{{ memory.title }}</p><p v-if="memory.description" class="mt-1 line-clamp-2 text-[10px] leading-5 text-[#6b6b65]">{{ memory.description }}</p></div>
                <span class="shrink-0 text-[8px] uppercase tracking-[0.12em] text-[#a38b68]">{{ memory.category || 'Arsip' }}</span>
            </div>
        </a>
    </div>
</template>

<style>
.pswp__archive-caption {
    position: absolute;
    inset-inline: 0;
    bottom: 24px;
    padding-inline: 20px;
    color: white;
    font: 11px/1.5 'Inter', sans-serif;
    text-align: center;
}
</style>