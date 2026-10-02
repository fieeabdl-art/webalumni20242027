<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import SectionFade from './SectionFade.vue';

const props = defineProps({
    teachers: { type: Array, default: () => [] },
});

gsap.registerPlugin(ScrollTrigger);

const section = ref(null);
const slider = ref(null);
const activeIndex = ref(0);
const activeTeacher = computed(() => props.teachers[activeIndex.value] ?? null);
let swiperInstance;
let animationContext;

async function initializeSlider() {
    if (!section.value) {
        return;
    }

    if (props.teachers.length > 1 && slider.value) {
        const [{ default: Swiper }, { A11y, EffectCoverflow, Keyboard }] = await Promise.all([
            import('swiper'),
            import('swiper/modules'),
        ]);

        if (!slider.value || !section.value) {
            return;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        swiperInstance = new Swiper(slider.value, {
            modules: [A11y, EffectCoverflow, Keyboard],
            a11y: { enabled: true },
            centeredSlides: true,
            coverflowEffect: {
                rotate: 35,
                stretch: 0,
                depth: 180,
                modifier: 1,
                slideShadows: false,
            },
            effect: 'coverflow',
            grabCursor: true,
            initialSlide: Math.floor(props.teachers.length / 2),
            keyboard: { enabled: true, onlyInViewport: true },
            loop: false,
            rewind: props.teachers.length >= 5,
            slidesPerView: 'auto',
            speed: prefersReducedMotion ? 0 : 700,
            breakpoints: {
                0: {
                    coverflowEffect: {
                        rotate: 16,
                        stretch: 0,
                        depth: 90,
                        modifier: 1,
                        slideShadows: false,
                    },
                },
                768: {
                    coverflowEffect: {
                        rotate: 28,
                        stretch: 0,
                        depth: 140,
                        modifier: 1,
                        slideShadows: false,
                    },
                },
                1200: {
                    coverflowEffect: {
                        rotate: 35,
                        stretch: 0,
                        depth: 180,
                        modifier: 1,
                        slideShadows: false,
                    },
                },
            },
            on: {
                realIndexChange(instance) {
                    activeIndex.value = instance.realIndex;
                },
            },
        });
        activeIndex.value = swiperInstance.realIndex;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cards = section.value.querySelectorAll('[data-coverflow-card]');

    if (!prefersReducedMotion && cards.length > 0) {
        animationContext = gsap.context(() => {
            gsap.fromTo(cards, {
                opacity: 0,
                scale: 0.9,
            }, {
                opacity: 1,
                scale: 1,
                duration: 0.65,
                stagger: 0.08,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: section.value,
                    start: 'top 82%',
                    once: true,
                },
            });
            gsap.fromTo('[data-teacher-label]', {
                clipPath: 'inset(0 100% 0 0)',
            }, {
                clipPath: 'inset(0 0% 0 0)',
                duration: 0.7,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: section.value,
                    start: 'top 82%',
                    once: true,
                },
            });
        }, section.value);
    }
}

function goToSlide(index) {
    if (!swiperInstance) {
        return;
    }

    if (swiperInstance.params.loop) {
        swiperInstance.slideToLoop(index);
        return;
    }

    swiperInstance.slideTo(index);
}

onMounted(() => {
    void initializeSlider();
});

onBeforeUnmount(() => {
    animationContext?.revert();
    swiperInstance?.destroy(true, true);
});
</script>

<template>
    <section
        id="guru"
        ref="section"
        class="teacher-coverflow relative isolate overflow-hidden bg-[#151515] px-6 py-24 text-[#f5f2ec] sm:px-10 sm:py-32 lg:px-14"
        aria-labelledby="teacher-coverflow-title"
    >
        <SectionFade />
        <div class="teacher-coverflow__texture pointer-events-none absolute inset-0 z-0" aria-hidden="true"></div>

        <div class="relative z-10 mx-auto max-w-[1400px]">
            <header class="flex flex-wrap items-end justify-between gap-7">
                <div class="max-w-3xl">
                    <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#d7d1c7]">
                        <span class="h-px w-9 bg-[#b2946c]" aria-hidden="true"></span>
                        Mereka yang membersamai
                    </p>
                    <h2 id="teacher-coverflow-title" class="mt-5 font-editorial text-5xl leading-none sm:text-7xl">GURU KAMI</h2>
                </div>
                <Link
                    href="/guru"
                    class="focus-ring inline-flex min-h-11 items-center gap-3 border-b border-white/50 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] transition-colors hover:border-[#c15c52] hover:text-white"
                >
                    Lihat semua guru
                    <ChevronRight :size="16" aria-hidden="true" />
                </Link>
            </header>

            <div v-if="teachers.length === 0" class="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:gap-8">
                <div
                    v-for="frame in 3"
                    :key="frame"
                    class="teacher-coverflow__empty archive-grain relative grid aspect-[3/4] place-items-center border border-white/10 bg-white/[0.035] px-4 text-center text-[10px] font-medium uppercase tracking-[0.14em] text-[#d7d1c7]"
                >
                    <span class="teacher-coverflow__empty-mark" aria-hidden="true"></span>
                    <span class="relative">DATA BELUM TERSEDIA</span>
                </div>
            </div>

            <div v-else-if="teachers.length === 1" class="mt-14 flex justify-center">
                <article
                    class="teacher-slide teacher-slide--active group relative aspect-[3/4] w-[min(82vw,320px)] overflow-hidden rounded-sm bg-[#242424] shadow-[0_24px_60px_rgba(0,0,0,0.4)]"
                    data-coverflow-card
                >
                    <div class="teacher-slide__surface relative h-full w-full overflow-hidden rounded-sm">
                        <img
                            v-if="teachers[0].photo_url"
                            :src="teachers[0].photo_url"
                            :alt="teachers[0].name"
                            class="teacher-slide__photo absolute inset-0 h-full w-full object-cover"
                            loading="eager"
                            fetchpriority="high"
                        >
                        <div v-else class="archive-grain grid h-full place-items-center px-5 text-center text-[10px] font-medium uppercase tracking-[0.12em] text-[#d7d1c7]">
                            DATA BELUM TERSEDIA
                        </div>
                        <span data-teacher-label class="teacher-slide__label absolute left-0 top-5 max-w-[86%] truncate bg-[#a8433b] px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.16em] text-white">
                            {{ (teachers[0].subject || teachers[0].role || 'DATA BELUM TERSEDIA').toLocaleUpperCase('id') }}
                        </span>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent px-5 pb-5 pt-16">
                            <p class="font-editorial text-2xl">{{ teachers[0].name }}</p>
                            <p class="mt-2 text-[10px] font-medium uppercase tracking-[0.12em] text-white/80">{{ teachers[0].role || teachers[0].subject || 'DATA BELUM TERSEDIA' }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="mt-14">
                <div class="teacher-coverflow__mask">
                    <div
                        ref="slider"
                        class="swiper teacher-coverflow__swiper"
                        role="region"
                        aria-roledescription="carousel"
                        aria-label="Guru kami"
                    >
                        <div class="swiper-wrapper">
                            <article
                                v-for="(teacher, index) in teachers"
                                :key="teacher.id"
                                class="swiper-slide teacher-slide group relative aspect-[3/4] cursor-pointer overflow-hidden rounded-sm bg-[#242424] shadow-[0_20px_50px_rgba(0,0,0,0.35)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#d7b68e]"
                                data-coverflow-card
                                role="group"
                                aria-roledescription="slide"
                                :aria-label="`${index + 1} dari ${teachers.length}: ${teacher.name}`"
                                tabindex="0"
                                @click="goToSlide(index)"
                                @keydown.enter.prevent="goToSlide(index)"
                            >
                                <div class="teacher-slide__surface relative h-full w-full overflow-hidden rounded-sm">
                                    <img
                                        v-if="teacher.photo_url"
                                        :src="teacher.photo_url"
                                        :alt="teacher.name"
                                        class="teacher-slide__photo absolute inset-0 h-full w-full object-cover"
                                        :loading="index === activeIndex ? 'eager' : 'lazy'"
                                        :fetchpriority="index === activeIndex ? 'high' : 'auto'"
                                    >
                                    <div v-else class="archive-grain grid h-full place-items-center px-5 text-center text-[10px] font-medium uppercase tracking-[0.12em] text-[#d7d1c7]">
                                        DATA BELUM TERSEDIA
                                    </div>
                                    <span data-teacher-label class="teacher-slide__label absolute left-0 top-5 max-w-[86%] truncate bg-[#a8433b] px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.16em] text-white">
                                        {{ (teacher.subject || teacher.role || 'DATA BELUM TERSEDIA').toLocaleUpperCase('id') }}
                                    </span>
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent px-4 pb-5 pt-16 sm:px-5">
                                        <p class="font-editorial text-xl sm:text-2xl">{{ teacher.name }}</p>
                                        <p class="mt-2 truncate text-[9px] font-medium uppercase tracking-[0.12em] text-white/80">{{ teacher.role || teacher.subject || 'DATA BELUM TERSEDIA' }}</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>

                <div
                    v-if="activeTeacher"
                    class="mx-auto mt-8 min-h-32 max-w-2xl text-center"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    <Transition name="teacher-copy" mode="out-in">
                        <div :key="activeTeacher.id">
                            <h3 class="font-editorial text-2xl sm:text-3xl">{{ activeTeacher.name }}</h3>
                            <p class="mt-2 text-[10px] font-medium uppercase tracking-[0.15em] text-[#d7d1c7]">
                                {{ activeTeacher.role || activeTeacher.subject || 'DATA BELUM TERSEDIA' }}
                            </p>
                            <blockquote class="mx-auto mt-4 max-w-xl font-editorial text-lg italic leading-7 text-white/80">
                                {{ activeTeacher.quote || 'DATA BELUM TERSEDIA' }}
                            </blockquote>
                        </div>
                    </Transition>
                </div>

                <div v-if="teachers.length > 1" class="mt-7 flex items-center justify-center gap-6">
                    <button
                        type="button"
                        class="focus-ring grid size-12 place-items-center rounded-full border border-white/25 text-white transition-colors hover:border-white hover:bg-white/10"
                        aria-label="Guru sebelumnya"
                        @click="swiperInstance?.slidePrev()"
                    >
                        <ChevronLeft :size="22" aria-hidden="true" />
                    </button>
                    <span class="text-[9px] font-medium uppercase tracking-[0.18em] text-white/55">Jelajahi potret</span>
                    <button
                        type="button"
                        class="focus-ring grid size-12 place-items-center rounded-full border border-white/25 text-white transition-colors hover:border-white hover:bg-white/10"
                        aria-label="Guru berikutnya"
                        @click="swiperInstance?.slideNext()"
                    >
                        <ChevronRight :size="22" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <div v-if="teachers.length === 1" class="mx-auto mt-8 min-h-32 max-w-2xl text-center" aria-live="polite">
                <p class="font-editorial text-2xl">{{ teachers[0].name }}</p>
                <p class="mt-2 text-[10px] font-medium uppercase tracking-[0.15em] text-[#d7d1c7]">
                    {{ teachers[0].role || teachers[0].subject || 'DATA BELUM TERSEDIA' }}
                </p>
                <blockquote class="mx-auto mt-4 max-w-xl font-editorial text-lg italic leading-7 text-white/80">
                    {{ teachers[0].quote || 'DATA BELUM TERSEDIA' }}
                </blockquote>
            </div>
        </div>
    </section>
</template>

<style scoped>
.teacher-coverflow__texture {
    opacity: 0.14;
    background-image:
        radial-gradient(ellipse at 50% 45%, transparent 35%, #000 100%),
        radial-gradient(#c7bba8 0.45px, transparent 0.8px);
    background-position: center, 0 0;
    background-size: auto, 5px 5px;
}

.teacher-coverflow__mask {
    overflow: visible;
    padding: 0.75rem 0 1.5rem;
}

.teacher-coverflow__swiper {
    width: 52%;
    margin-inline: auto;
    overflow: visible;
}

.teacher-coverflow__swiper :deep(.swiper-wrapper) {
    display: flex;
    align-items: center;
}

.teacher-coverflow__swiper :deep(.swiper-slide) {
    position: relative;
    display: block;
    flex-shrink: 0;
}

.teacher-slide {
    z-index: 0;
}

.teacher-slide.swiper-slide {
    width: clamp(185px, 17vw, 245px);
}

.teacher-slide__surface {
    transform: scale(0.92);
    transition: transform 600ms cubic-bezier(0.2, 0.75, 0.25, 1);
}

.teacher-slide__photo {
    filter: grayscale(1) contrast(1.05) brightness(0.88);
    transition: filter 550ms ease, transform 700ms cubic-bezier(0.2, 0.75, 0.25, 1);
}

:deep(.swiper-slide-active) .teacher-slide__photo {
    filter: grayscale(0) contrast(1) brightness(1);
}

.teacher-slide--active .teacher-slide__surface,
:deep(.swiper-slide-active) .teacher-slide__surface {
    transform: scale(1.04);
}

.teacher-slide--active,
:deep(.swiper-slide-active) {
    z-index: 2;
}

.teacher-slide__label {
    z-index: 2;
    color: #fff;
}

.teacher-coverflow__empty {
    background-image:
        radial-gradient(ellipse at 50% 45%, transparent 35%, #000 100%),
        radial-gradient(#c7bba8 0.45px, transparent 0.8px);
    background-size: auto, 5px 5px;
}

.teacher-coverflow__empty-mark {
    position: absolute;
    inset: 1rem;
    border: 1px solid rgb(255 255 255 / 12%);
}

.teacher-copy-enter-active,
.teacher-copy-leave-active {
    transition: opacity 180ms ease, transform 180ms ease;
}

.teacher-copy-enter-from,
.teacher-copy-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

@media (min-width: 768px) and (hover: hover) and (pointer: fine) {
    :deep(.swiper-slide:not(.swiper-slide-active):hover) .teacher-slide__photo {
        filter: grayscale(0.45) contrast(1) brightness(0.96);
    }
}

@media (min-width: 640px) and (max-width: 1199px) {
    .teacher-coverflow__mask {
        width: 90%;
        margin-inline: auto;
        overflow: hidden;
    }

    .teacher-coverflow__swiper {
        width: 100%;
    }

    .teacher-slide.swiper-slide {
        width: calc(100% / 3 + 1px);
    }
}

@media (max-width: 767px) {
    .teacher-coverflow__mask {
        width: 100%;
        overflow: visible;
    }

    .teacher-coverflow__swiper {
        width: 100%;
    }

    .teacher-slide.swiper-slide {
        width: 72vw;
    }
}

@media (prefers-reduced-motion: reduce) {
    .teacher-slide,
    .teacher-slide__surface,
    .teacher-slide__photo,
    .teacher-copy-enter-active,
    .teacher-copy-leave-active {
        transition-duration: 0.01ms !important;
    }
}
</style>
