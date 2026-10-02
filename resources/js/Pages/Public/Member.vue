<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';

defineProps({
    member: { type: Object, required: true },
});
</script>

<template>
    <Head :title="member.name">
        <meta name="description" :content="member.bio || member.quote || 'DATA BELUM TERSEDIA'">
    </Head>
    <PublicLayout>
        <section class="bg-[#171612] px-6 pb-16 pt-32 text-[#f5f2ec] sm:px-10 sm:pb-24 sm:pt-40 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <Link href="/anggota" class="focus-ring inline-flex items-center gap-2 text-[9px] uppercase tracking-[0.16em] text-white/55 transition-colors hover:text-white">
                    <ArrowLeft :size="14" /> Kembali ke anggota
                </Link>
                <div class="mt-10 grid items-end gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:gap-16">
                    <div class="relative z-10 pb-4">
                        <SectionEyebrow number="01" label="Wajah di dalam cerita" light />
                        <p class="mt-10 text-[9px] uppercase tracking-[0.18em] text-[#c4a982]">
                            {{ member.class_name || 'DATA BELUM TERSEDIA' }} · {{ member.major || 'DATA BELUM TERSEDIA' }}
                        </p>
                        <h1 class="mt-4 max-w-3xl font-editorial text-6xl leading-[0.92] sm:text-8xl lg:-mr-24 lg:text-[7.5rem]">{{ member.name }}</h1>
                        <p v-if="member.nickname" class="mt-5 font-editorial text-2xl italic text-white/55">“{{ member.nickname }}”</p>
                    </div>
                    <figure class="relative aspect-[4/5] max-h-[780px] overflow-hidden bg-[#28251f] sm:aspect-[5/4] lg:aspect-[4/5]">
                        <img v-if="member.photo_url" :src="member.photo_url" :alt="`Potret ${member.name}`" fetchpriority="high" class="h-full w-full object-cover">
                        <div v-else class="archive-grain grid h-full place-items-center text-[10px] uppercase tracking-[0.18em] text-white/50">DATA BELUM TERSEDIA</div>
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-black/10"></div>
                        <figcaption class="absolute bottom-4 left-4 text-[8px] uppercase tracking-[0.16em] text-white/65">Arsip anggota · {{ member.id }}</figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <section class="px-6 py-20 sm:px-10 sm:py-28 lg:px-14">
            <div class="mx-auto grid max-w-[1400px] gap-10 lg:grid-cols-[0.7fr_1.3fr] lg:gap-20">
                <div>
                    <SectionEyebrow number="02" label="Catatan personal" />
                    <p class="mt-5 text-[9px] uppercase tracking-[0.14em] text-[#6b6b65]">Satu bagian dari cerita bersama</p>
                </div>
                <div>
                    <blockquote class="font-editorial text-3xl leading-[1.25] sm:text-5xl">
                        {{ member.quote ? `“${member.quote}”` : 'DATA BELUM TERSEDIA' }}
                    </blockquote>
                    <p class="mt-10 max-w-3xl whitespace-pre-line text-sm leading-8 text-[#6b6b65]">
                        {{ member.bio || 'DATA BELUM TERSEDIA' }}
                    </p>
                    <a v-if="member.instagram" :href="member.instagram" target="_blank" rel="noreferrer" class="focus-ring mt-10 inline-flex items-center gap-2 border-b border-black/20 pb-2 text-[9px] uppercase tracking-[0.16em]">
                        Instagram <ArrowUpRight :size="14" />
                    </a>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
