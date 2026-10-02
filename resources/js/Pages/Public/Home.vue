<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUpRight } from '@lucide/vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    home: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    members: { type: Array, default: () => [] },
    teachers: { type: Array, default: () => [] },
    memories: { type: Array, default: () => [] },
});

const hero = ref(null);
const heroImage = ref(null);
const heroTitle = computed(() => props.home.hero_title || 'DATA BELUM TERSEDIA');
let animationContext;

onMounted(() => {
    const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    animationContext = gsap.context(() => {
        if (isReducedMotion) {
            gsap.from('[data-reveal]', {
                opacity: 0,
                duration: 0.25,
                stagger: 0.05,
                ease: 'power3.out',
            });

            return;
        }

        gsap.from('[data-reveal]', {
            y: 26,
            opacity: 0,
            duration: 0.85,
            stagger: 0.12,
            ease: 'power3.out',
            delay: 0.15,
        });
        gsap.utils.toArray('[data-scroll-reveal]').forEach((element) => {
            gsap.from(element, {
                y: 30,
                opacity: 0,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: { trigger: element, start: 'top 88%' },
            });
        });

        if (heroImage.value) {
            gsap.timeline()
                .fromTo(heroImage.value, {
                    clipPath: 'inset(0 0 100% 0)',
                    scale: 1.08,
                }, {
                    clipPath: 'inset(0 0 0% 0)',
                    duration: 1.5,
                    ease: 'power4.inOut',
                })
                .to(heroImage.value, {
                    scale: isMobile ? 1.06 : 1.12,
                    duration: isMobile ? 8 : 14,
                    ease: 'none',
                }, 0);
        }

        if (hero.value && !isMobile) {
            gsap.to(hero.value, {
                yPercent: 10,
                ease: 'none',
                scrollTrigger: { trigger: hero.value, start: 'top top', end: 'bottom top', scrub: true },
            });
        }
    });
});

onUnmounted(() => {
    animationContext?.revert();
});
</script>

<template>
    <PublicLayout :site="site">
        <section class="relative flex min-h-[760px] h-[100svh] max-h-[1100px] items-end overflow-hidden bg-[#171612] text-[#f5f2ec]">
            <div ref="hero" class="absolute inset-0 origin-center">
                <img v-if="home.hero_image_url" ref="heroImage" :src="home.hero_image_url" alt="Foto sampul arsip angkatan" class="h-full w-full object-cover will-change-transform" fetchpriority="high">
                <div v-else class="archive-grain absolute inset-0 bg-[radial-gradient(ellipse_at_58%_32%,#625443_0%,#27241f_38%,#171612_76%)]">
                    <div class="absolute inset-x-[12%] top-[19%] h-[53%] border border-white/10 sm:inset-x-[24%] sm:top-[15%] sm:h-[60%]">
                        <div class="absolute inset-3 border border-white/10 sm:inset-5"></div>
                        <div class="absolute bottom-5 left-5 text-[8px] uppercase tracking-[0.24em] text-white/40 sm:bottom-8 sm:left-8">Arsip visual · {{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</div>
                    </div>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#111]/90 via-[#111]/25 to-[#111]/20"></div>
                <div class="archive-grain pointer-events-none absolute inset-0 opacity-30 mix-blend-screen"></div>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_30%,#111_100%)] opacity-50"></div>
            </div>
            <div class="relative z-10 mx-auto w-full max-w-[1600px] px-6 pb-14 pt-32 sm:px-10 sm:pb-20 lg:px-14 lg:pb-[8vh]">
                <div class="max-w-5xl">
                    <p data-reveal class="mb-5 text-[9px] font-semibold uppercase tracking-[0.24em] text-white/65 sm:text-[10px]">Arsip digital · {{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</p>
                    <h1 data-reveal class="max-w-5xl font-editorial text-[clamp(3.25rem,9vw,8.6rem)] leading-[0.9]">{{ heroTitle }}</h1>
                    <p data-reveal class="mt-6 max-w-lg text-sm leading-7 text-white/75 sm:mt-8 sm:text-base">{{ home.hero_subtitle || 'DATA BELUM TERSEDIA' }}</p>
                    <a data-reveal href="#cerita" class="focus-ring mt-8 inline-flex min-h-12 items-center gap-4 border border-white/40 px-5 text-[9px] font-semibold uppercase tracking-[0.18em] transition-colors hover:bg-white hover:text-[#242424] sm:mt-10">
                        {{ home.cta_text || 'JELAJAHI CERITA' }} <ArrowDown :size="15" />
                    </a>
                </div>
                <div class="mt-14 flex items-end justify-between border-t border-white/25 pt-4 text-[8px] uppercase tracking-[0.16em] text-white/55 sm:mt-20">
                    <span>{{ site.school_name || 'DATA BELUM TERSEDIA' }}</span>
                    <span class="hidden sm:block">Satu ruang untuk mengingat</span>
                    <span>01 / 05</span>
                </div>
            </div>
        </section>

        <section id="cerita" class="px-6 py-24 sm:px-10 sm:py-32 lg:px-14 lg:py-40">
            <div class="mx-auto grid max-w-[1400px] gap-12 lg:grid-cols-[0.7fr_1.3fr] lg:gap-16">
                <SectionEyebrow number="01" label="Sebuah pengantar" />
                <div data-scroll-reveal>
                    <h2 class="font-editorial text-4xl leading-[1.12] sm:text-6xl lg:text-7xl">{{ home.intro_title || 'DATA BELUM TERSEDIA' }}</h2>
                    <p class="mt-8 max-w-2xl text-sm leading-7 text-[#6b6b65] sm:text-base sm:leading-8">{{ home.intro_description || 'DATA BELUM TERSEDIA' }}</p>
                </div>
            </div>
        </section>

        <section class="bg-[#ede8de] px-6 py-16 sm:px-10 sm:py-20 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="02" label="Yang pernah kita bagi" />
                <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-10 border-t border-black/15 pt-7 md:grid-cols-4 md:gap-8">
                    <div v-for="item in [
                        { label: 'Anggota', value: stats.members },
                        { label: 'Kelas', value: stats.classes },
                        { label: 'Guru', value: stats.teachers },
                        { label: 'Kenangan', value: stats.memories },
                    ]" :key="item.label" class="min-h-24">
                        <p class="font-editorial text-4xl sm:text-5xl">{{ Number.isInteger(item.value) ? item.value : '—' }}</p>
                        <p class="mt-3 text-[9px] uppercase tracking-[0.16em] text-[#6b6b65]">{{ item.label }}</p>
                        <span v-if="!Number.isInteger(item.value)" class="mt-2 block text-[8px] uppercase tracking-[0.12em] text-[#8b4b45]">Data belum tersedia</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-6 py-24 sm:px-10 sm:py-32 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div><SectionEyebrow number="03" label="Wajah di dalam cerita" /><h2 class="mt-5 font-editorial text-4xl sm:text-6xl">Inilah kami.</h2></div>
                    <Link href="/anggota" class="focus-ring inline-flex items-center gap-2 border-b border-[#242424] pb-2 text-[9px] font-semibold uppercase tracking-[0.16em]">Lihat semua anggota <ArrowUpRight :size="14" /></Link>
                </div>
                <div v-if="members.length" class="mt-12 grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="(member, index) in members" :key="member.id" class="group" :class="index % 3 === 1 ? 'lg:translate-y-12' : ''">
                        <div class="relative aspect-[4/5] overflow-hidden bg-[#ded8cd]">
                            <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]">
                            <div v-else class="archive-grain grid h-full place-items-center text-[9px] uppercase tracking-[0.18em] text-[#6b6b65]">DATA BELUM TERSEDIA</div>
                        </div>
                        <p class="mt-4 font-editorial text-xl">{{ member.name }}</p>
                        <p class="mt-1 text-[9px] uppercase tracking-[0.12em] text-[#6b6b65]">{{ member.class_name || 'DATA BELUM TERSEDIA' }} · {{ member.major || 'DATA BELUM TERSEDIA' }}</p>
                    </article>
                </div>
                <p v-else class="mt-12 border-y border-black/15 py-7 text-[10px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</p>
            </div>
        </section>

        <section class="bg-[#242424] px-6 py-24 text-[#f5f2ec] sm:px-10 sm:py-32 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div><SectionEyebrow number="04" label="Mereka yang membersamai" light /><h2 class="mt-5 font-editorial text-4xl sm:text-6xl">Guru kami.</h2></div>
                    <Link href="/guru" class="focus-ring inline-flex items-center gap-2 border-b border-white/50 pb-2 text-[9px] font-semibold uppercase tracking-[0.16em]">Kenali guru kami <ArrowUpRight :size="14" /></Link>
                </div>
                <div v-if="teachers.length" class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="teacher in teachers" :key="teacher.id" class="grid grid-cols-[92px_1fr] items-center gap-5 border-t border-white/20 py-5 sm:grid-cols-[120px_1fr]">
                        <div class="aspect-[4/5] overflow-hidden bg-white/10">
                            <img v-if="teacher.photo_url" :src="teacher.photo_url" :alt="teacher.name" loading="lazy" class="h-full w-full object-cover">
                            <div v-else class="grid h-full place-items-center text-[8px] uppercase tracking-[0.1em] text-white/45">Belum ada foto</div>
                        </div>
                        <div><p class="font-editorial text-xl">{{ teacher.name }}</p><p class="mt-2 text-[9px] uppercase tracking-[0.12em] text-white/55">{{ teacher.subject || 'DATA BELUM TERSEDIA' }}</p></div>
                    </article>
                </div>
                <p v-else class="mt-12 border-y border-white/20 py-7 text-[10px] uppercase tracking-[0.16em] text-white/50">DATA BELUM TERSEDIA</p>
            </div>
        </section>

        <section class="px-6 py-24 sm:px-10 sm:py-32 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div><SectionEyebrow number="05" label="Potongan yang tersimpan" /><h2 class="mt-5 font-editorial text-4xl sm:text-6xl">Kenangan kami.</h2></div>
                    <Link href="/kenangan" class="focus-ring inline-flex items-center gap-2 border-b border-[#242424] pb-2 text-[9px] font-semibold uppercase tracking-[0.16em]">Buka arsip foto <ArrowUpRight :size="14" /></Link>
                </div>
                <div v-if="memories.length" class="mt-12 columns-1 gap-5 sm:columns-2 lg:columns-3">
                    <a v-for="memory in memories" :key="memory.id" :href="`/kenangan#${memory.slug}`" class="group mb-5 block break-inside-avoid overflow-hidden bg-[#ded8cd]">
                        <img v-if="memory.image_url" :src="memory.image_url" :alt="memory.title" loading="lazy" class="w-full transition-transform duration-700 group-hover:scale-[1.03]">
                        <div v-else class="grid aspect-[4/3] place-items-center text-[9px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</div>
                        <p class="px-3 py-3 text-[9px] uppercase tracking-[0.14em]">{{ memory.title }}</p>
                    </a>
                </div>
                <p v-else class="mt-12 border-y border-black/15 py-7 text-[10px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</p>
            </div>
        </section>

        <section class="relative overflow-hidden bg-[#111] px-6 py-24 text-[#f5f2ec] sm:px-10 sm:py-32 lg:px-14 lg:py-40">
            <div class="archive-grain pointer-events-none absolute inset-0 opacity-60"></div>
            <div class="relative mx-auto max-w-[1400px]">
                <SectionEyebrow label="Sampai di sini, untuk sekarang" light />
                <h2 class="mt-10 max-w-5xl font-editorial text-4xl leading-[1.08] sm:text-6xl lg:text-7xl">{{ home.closing_title || 'DATA BELUM TERSEDIA' }}</h2>
                <p class="mt-7 max-w-xl text-sm leading-7 text-white/60">{{ home.closing_description || 'DATA BELUM TERSEDIA' }}</p>
                <div class="mt-16 flex flex-wrap items-end justify-between gap-6 border-t border-white/20 pt-5">
                    <p class="font-editorial text-2xl">{{ site.cohort_name || 'DATA BELUM TERSEDIA' }}</p>
                    <p class="text-[9px] uppercase tracking-[0.2em] text-white/55">{{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>