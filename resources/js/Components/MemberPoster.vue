<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const props = defineProps({
    member: { type: Object, required: true },
    variant: { type: String, default: 'full' },
    index: { type: Number, default: 0 },
    site: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['open']);
const poster = ref(null);
const hasCutout = computed(() => props.member.poster_uses_cutout === true);
const posterUrl = computed(() => props.member.poster_url || null);
const fullName = computed(() => props.member.name?.trim() || 'DATA BELUM TERSEDIA');
const displayName = computed(() => props.member.nickname?.trim() || fullName.value.split(/\s+/)[0]);
const titleLines = computed(() => {
    const words = displayName.value.split(/\s+/);

    if (displayName.value.length <= 12 || words.length < 2) {
        return [displayName.value];
    }

    const splitAt = Math.ceil(words.length / 2);

    return [words.slice(0, splitAt).join(' '), words.slice(splitAt).join(' ')];
});
const posterStyle = computed(() => ({
    '--poster-tilt': `${((props.index % 5) - 2) * 0.7}deg`,
}));
let animationContext;
let preloadObserver;
let preloadIdleCallback;
let preloadTimeout;
let originalPreloaded = false;

function openDetails() {
    const origin = poster.value?.querySelector('.member-poster__portrait') ?? poster.value;
    const bounds = origin?.getBoundingClientRect();

    emit('open', {
        member: props.member,
        originRect: bounds ? {
            left: bounds.left,
            top: bounds.top,
            width: bounds.width,
            height: bounds.height,
        } : null,
    });
}

function preloadOriginal() {
    if (originalPreloaded || !props.member.original_url) {
        return;
    }

    originalPreloaded = true;
    const image = new Image();
    image.decoding = 'async';
    image.src = props.member.original_url;
}

function scheduleOriginalPreload() {
    if ('requestIdleCallback' in window) {
        preloadIdleCallback = window.requestIdleCallback(preloadOriginal, { timeout: 1200 });
    } else {
        preloadTimeout = window.setTimeout(preloadOriginal, 250);
    }
}

onMounted(() => {
    gsap.registerPlugin(ScrollTrigger);

    if ('IntersectionObserver' in window && poster.value) {
        preloadObserver = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                preloadObserver?.disconnect();
                scheduleOriginalPreload();
            }
        }, { rootMargin: '160px' });
        preloadObserver.observe(poster.value);
    } else {
        scheduleOriginalPreload();
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !poster.value) {
        return;
    }

    animationContext = gsap.context(() => {
        const entrance = gsap.timeline({
            scrollTrigger: {
                trigger: poster.value,
                start: 'top 88%',
                once: true,
            },
        });

        entrance
            .fromTo(poster.value, { clipPath: 'inset(100% 0 0 0)' }, {
                clipPath: 'inset(0% 0 0 0)',
                duration: 0.75,
                ease: 'power3.out',
            })
            .fromTo('[data-poster-title]', { yPercent: 24, opacity: 0 }, {
                yPercent: 0,
                opacity: 1,
                duration: 0.55,
                ease: 'power3.out',
            }, '-=0.4')
            .fromTo('[data-poster-person]', { yPercent: 10, opacity: 0 }, {
                yPercent: 0,
                opacity: 1,
                duration: 0.65,
                ease: 'power3.out',
            }, '-=0.3')
            .fromTo('[data-poster-ornament]', { opacity: 0 }, {
                opacity: 0.65,
                duration: 0.5,
            }, '-=0.45')
            .fromTo('[data-poster-label]', { clipPath: 'inset(0 100% 0 0)' }, {
                clipPath: 'inset(0 0% 0 0)',
                duration: 0.45,
                ease: 'power2.out',
            }, '-=0.3');
    }, poster.value);
});

onBeforeUnmount(() => {
    animationContext?.revert();
    preloadObserver?.disconnect();

    if (preloadIdleCallback !== undefined && 'cancelIdleCallback' in window) {
        window.cancelIdleCallback(preloadIdleCallback);
    }

    if (preloadTimeout !== undefined) {
        window.clearTimeout(preloadTimeout);
    }
});
</script>

<template>
    <article
        ref="poster"
        class="member-poster group relative isolate aspect-[3/4] w-full overflow-hidden bg-[#181310] text-[#f5f2ec]"
        :class="variant === 'compact' ? 'member-poster--compact' : ''"
        :style="posterStyle"
    >
        <h3 class="sr-only">{{ fullName }}</h3>
        <button
            type="button"
            class="member-poster__button focus-ring absolute inset-0 block h-full w-full overflow-hidden text-left"
            :aria-label="`Lihat profil ${fullName}${member.nickname ? `, dipanggil ${member.nickname}` : ''}`"
            aria-haspopup="dialog"
            @click="openDetails"
            @mouseenter="preloadOriginal"
            @focusin="preloadOriginal"
        >
            <span class="member-poster__background absolute inset-0" aria-hidden="true"></span>
            <span class="member-poster__grain archive-grain pointer-events-none absolute inset-0" aria-hidden="true"></span>
            <span class="member-poster__ornament member-poster__ornament--left" data-poster-ornament aria-hidden="true"></span>
            <span class="member-poster__ornament member-poster__ornament--right" data-poster-ornament aria-hidden="true"></span>
            <span class="member-poster__cohort absolute left-[6%] right-[6%] top-[5%] z-10 flex justify-between gap-2 border-b border-white/30 pb-[2.5%] text-[clamp(6px,1.8cqw,10px)] font-semibold uppercase tracking-[0.18em] text-white/75">
                <span class="truncate">{{ site.cohort_name || 'DATA BELUM TERSEDIA' }}</span>
                <span class="shrink-0">{{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</span>
            </span>
            <span
                class="member-poster__giant absolute left-[5%] right-[5%] z-[2] flex flex-col items-center justify-center text-center font-editorial font-black uppercase leading-[0.78] text-[#f5f2ec]"
                :class="hasCutout ? 'top-[25%] h-[42%]' : 'top-[13%] h-[30%]'"
                aria-hidden="true"
                data-poster-title
            >
                <span v-for="(line, lineIndex) in titleLines" :key="`${line}-${lineIndex}`" class="member-poster__title-line max-w-full break-words">
                    {{ line }}
                </span>
            </span>
            <span class="member-poster__signature absolute left-[7%] top-[41%] z-[4] max-w-[78%] -rotate-3 font-editorial text-[clamp(11px,4cqw,21px)] italic text-[#d06a5f]" data-poster-label aria-hidden="true">
                {{ fullName }}
            </span>
            <span class="member-poster__slashes absolute left-0 right-0 top-[56%] z-[1] h-px -rotate-[13deg] bg-[#a8433b]/55" aria-hidden="true"></span>

            <span
                v-if="posterUrl"
                class="member-poster__portrait absolute bottom-[13%] left-[5%] z-[3] block w-[90%]"
                :class="hasCutout ? 'h-[76%]' : 'top-[39%] h-[48%]'"
                data-poster-person
            >
                <img
                    v-if="hasCutout"
                    :src="posterUrl"
                    :alt="fullName"
                    class="member-poster__cutout absolute inset-0 h-full w-full object-contain object-bottom"
                    :loading="index < 3 ? 'eager' : 'lazy'"
                    :fetchpriority="index < 3 ? 'high' : 'auto'"
                >
                <img
                    v-else
                    :src="posterUrl"
                    :alt="fullName"
                    class="member-poster__regular absolute inset-0 h-full w-full object-cover object-[50%_25%]"
                    :loading="index < 3 ? 'eager' : 'lazy'"
                    :fetchpriority="index < 3 ? 'high' : 'auto'"
                >
            </span>
            <span v-else class="absolute inset-x-0 bottom-[18%] z-[3] grid h-[32%] place-items-center px-5 text-center text-[clamp(8px,2.2cqw,12px)] font-semibold uppercase tracking-[0.15em] text-white/75" data-poster-person>
                DATA BELUM TERSEDIA
            </span>
            <span class="member-poster__foot-shadow absolute bottom-[13%] left-[20%] z-[2] h-[4%] w-[60%] rounded-[50%] bg-black/75" aria-hidden="true"></span>
            <span class="member-poster__photo-fade pointer-events-none absolute inset-x-0 bottom-0 z-[4] h-[39%]" aria-hidden="true"></span>

            <span class="absolute inset-x-[6%] bottom-[5%] z-[5] flex items-end justify-between gap-3 border-t border-white/35 pt-[3%] text-left">
                <span class="min-w-0">
                    <span class="block truncate text-[clamp(7px,2cqw,10px)] font-semibold uppercase tracking-[0.14em] text-white/90">
                        {{ member.instagram ? `@${member.instagram.split('/').filter(Boolean).at(-1)}` : ' ' }}
                    </span>
                    <span v-if="member.quote" class="member-poster__quote mt-1 line-clamp-2 block font-editorial text-[clamp(9px,2.6cqw,14px)] italic leading-tight text-white/85">
                        “{{ member.quote }}”
                    </span>
                </span>
                <span class="shrink-0 text-right text-[clamp(6px,1.7cqw,9px)] font-semibold uppercase leading-relaxed tracking-[0.12em] text-white/85">
                    <span class="block">{{ member.class_name || 'DATA BELUM TERSEDIA' }}</span>
                    <span class="block">{{ member.major || 'DATA BELUM TERSEDIA' }}</span>
                </span>
            </span>
        </button>
    </article>
</template>

<style scoped>
.member-poster {
    container-type: inline-size;
    transform: perspective(1200px) rotate(var(--poster-tilt));
    transition: transform 500ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 500ms ease;
    box-shadow: 0 18px 42px rgb(0 0 0 / 28%);
}

.member-poster:focus-within {
    outline: 2px solid #d7b68e;
    outline-offset: 5px;
}

.member-poster__background {
    background:
        radial-gradient(ellipse at 50% 100%, #3a1512 0%, transparent 65%),
        linear-gradient(135deg, #111 0%, #211714 62%, #3a1512 100%);
}

.member-poster__grain {
    opacity: 0.2;
    background-image:
        radial-gradient(#f5f2ec 0.45px, transparent 0.8px),
        radial-gradient(ellipse at 50% 45%, transparent 35%, #000 100%);
    background-size: 5px 5px, auto;
}

.member-poster__ornament {
    position: absolute;
    z-index: 1;
    width: 35%;
    height: 44%;
    border: 1px solid rgb(168 67 59 / 55%);
    opacity: 0.5;
    transition: opacity 500ms ease, transform 500ms ease;
}

.member-poster__ornament::before,
.member-poster__ornament::after {
    position: absolute;
    content: '';
    background: #a8433b;
}

.member-poster__ornament::before {
    top: 17%;
    left: -18%;
    width: 135%;
    height: 1px;
    transform: rotate(-28deg);
}

.member-poster__ornament::after {
    right: 12%;
    bottom: -8%;
    width: 1px;
    height: 50%;
    transform: rotate(18deg);
}

.member-poster__ornament--left {
    top: 21%;
    left: -22%;
    border-radius: 48% 52% 62% 38%;
    transform: rotate(-24deg);
}

.member-poster__ornament--right {
    right: -20%;
    bottom: 18%;
    border-radius: 62% 38% 40% 60%;
    transform: rotate(24deg);
}

.member-poster__giant {
    text-shadow:
        2px 3px 0 #7b2824,
        4px 5px 0 #521b18,
        7px 8px 0 rgb(0 0 0 / 75%);
    -webkit-text-stroke: 0.35px rgb(255 255 255 / 65%);
    transition: transform 500ms cubic-bezier(0.22, 1, 0.36, 1), text-shadow 500ms ease;
}

.member-poster__title-line {
    font-size: clamp(2rem, 19cqw, 5.2rem);
    letter-spacing: -0.075em;
}

.member-poster__signature {
    text-shadow: 0 2px 8px #111;
}

.member-poster__portrait {
    transition: transform 600ms cubic-bezier(0.22, 1, 0.36, 1);
}

.member-poster__cutout {
    filter: drop-shadow(0 18px 15px rgb(0 0 0 / 48%));
}

.member-poster__regular {
    -webkit-mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
    mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
}

.member-poster__foot-shadow {
    filter: blur(12px);
}

.member-poster__photo-fade {
    background: linear-gradient(to bottom, transparent, #171210 95%);
}

.member-poster__quote {
    display: none;
}

.member-poster--compact .member-poster__title-line {
    font-size: clamp(1.8rem, 17cqw, 4rem);
}

.member-poster--compact .member-poster__quote {
    display: -webkit-box;
}

.member-poster:hover .member-poster__quote,
.member-poster:focus-within .member-poster__quote {
    display: -webkit-box;
}

.member-poster:hover .member-poster__giant,
.member-poster:focus-within .member-poster__giant {
    transform: translate3d(0, -8px, 0);
    text-shadow:
        2px 4px 0 #9a342d,
        5px 7px 0 #521b18,
        9px 11px 0 rgb(0 0 0 / 85%);
}

.member-poster:hover .member-poster__portrait,
.member-poster:focus-within .member-poster__portrait {
    transform: scale(1.03);
}

.member-poster:hover .member-poster__ornament,
.member-poster:focus-within .member-poster__ornament {
    opacity: 0.82;
}

@media (hover: hover) and (pointer: fine) {
    .member-poster:hover {
        transform: perspective(1200px) rotate(var(--poster-tilt)) rotateX(2deg) rotateY(-2deg);
        box-shadow: 0 28px 56px rgb(0 0 0 / 36%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .member-poster,
    .member-poster__giant,
    .member-poster__portrait,
    .member-poster__ornament {
        transition-duration: 0.01ms !important;
    }

    .member-poster:hover {
        transform: perspective(1200px) rotate(var(--poster-tilt));
    }

    .member-poster:hover .member-poster__giant,
    .member-poster:hover .member-poster__portrait {
        transform: none;
    }
}
</style>
