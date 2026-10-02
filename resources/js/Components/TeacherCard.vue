<script setup>
defineProps({
    teacher: { type: Object, required: true },
});
</script>

<template>
    <article class="grid gap-5 border-t border-black/15 py-6 sm:grid-cols-[minmax(150px,0.7fr)_1.3fr] sm:gap-8">
        <div class="relative aspect-[4/5] overflow-hidden bg-[#e4ded3] sm:aspect-[5/6]">
            <img v-if="teacher.photo_url" :src="teacher.photo_url" :alt="teacher.name" loading="lazy" class="h-full w-full object-cover">
            <div v-else class="archive-grain grid h-full place-items-center px-4 text-center text-[9px] uppercase tracking-[0.14em] text-[#6b6b65]">DATA BELUM TERSEDIA</div>
        </div>
        <div class="flex flex-col justify-center">
            <p class="text-[9px] uppercase tracking-[0.16em] text-[#a38b68]">{{ teacher.subject || 'DATA BELUM TERSEDIA' }}</p>
            <h2 class="mt-3 font-editorial text-3xl leading-tight sm:text-4xl">{{ teacher.name }}</h2>
            <p v-if="teacher.role" class="mt-2 text-[10px] uppercase tracking-[0.12em] text-[#6b6b65]">{{ teacher.role }}</p>
            <blockquote v-if="teacher.quote" class="mt-7 font-editorial text-xl italic leading-8">“{{ teacher.quote }}”</blockquote>
            <p v-if="teacher.message" class="mt-5 max-w-xl text-sm leading-7 text-[#6b6b65]">{{ teacher.message }}</p>
        </div>
    </article>
</template>