<script setup>
defineProps({
    member: { type: Object, required: true },
});
</script>

<template>
    <article class="group">
        <div class="relative aspect-[4/5] overflow-hidden bg-[#e4ded3]">
            <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]">
            <div v-else class="archive-grain grid h-full place-items-center text-center text-[9px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</div>
            <a v-if="member.instagram" :href="member.instagram" target="_blank" rel="noreferrer" class="focus-ring absolute bottom-3 right-3 bg-[#f5f2ec] px-3 py-2 text-[8px] font-semibold uppercase tracking-[0.12em] opacity-0 transition-opacity group-hover:opacity-100">Instagram</a>
        </div>
        <div class="mt-4 flex items-baseline justify-between gap-3">
            <h2 class="font-editorial text-xl">{{ member.name }}</h2>
            <span v-if="member.nickname" class="text-[9px] text-[#6b6b65]">“{{ member.nickname }}”</span>
        </div>
        <p class="mt-1 text-[9px] uppercase tracking-[0.12em] text-[#6b6b65]">{{ member.class_name || 'DATA BELUM TERSEDIA' }} · {{ member.major || 'DATA BELUM TERSEDIA' }}</p>
        <blockquote v-if="member.quote" class="mt-4 max-w-sm font-editorial text-base italic leading-6 text-[#6b6b65]">“{{ member.quote }}”</blockquote>
        <p v-if="member.bio" class="mt-3 text-xs leading-6 text-[#6b6b65]">{{ member.bio }}</p>
    </article>
</template>