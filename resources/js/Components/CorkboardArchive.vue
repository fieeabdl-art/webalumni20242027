<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import { ArrowUpRight } from '@lucide/vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import 'photoswipe/style.css';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    memories: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
});

const stage = ref(null);
let isReducedMotion = false;
let isMobile = false;
let animationContext;
let lightbox;

const photoPositions = [
    { x: 3, y: 6, width: 21, rotation: -4, depth: 0.25 },
    { x: 27, y: 25, width: 20, rotation: 3, depth: 0.7 },
    { x: 51, y: 7, width: 21, rotation: -2, depth: 0.4 },
    { x: 75, y: 26, width: 21, rotation: 5, depth: 0.85 },
    { x: 14, y: 54, width: 21, rotation: 2, depth: 0.55 },
    { x: 61, y: 53, width: 22, rotation: -5, depth: 0.3 },
];
const threadConnections = [[0, 2], [2, 1], [1, 3], [3, 4], [4, 5]];

const laidOutMemories = computed(() => props.memories.map((memory, index) => ({
    ...memory,
    position: photoPositions[index % photoPositions.length],
})));

const threadPaths = computed(() => threadConnections
    .filter(([from, to]) => laidOutMemories.value.length > Math.max(from, to))
    .map(([from, to]) => {
        const start = laidOutMemories.value[from].position;
        const end = laidOutMemories.value[to].position;
        const x1 = (start.x + start.width / 2) * 10;
        const y1 = start.y * 8;
        const x2 = (end.x + end.width / 2) * 10;
        const y2 = end.y * 8;

        return `M ${x1} ${y1} Q ${(x1 + x2) / 2} ${(y1 + y2) / 2 - 45} ${x2} ${y2}`;
    }));

const photoStyle = (position, index) => ({
    '--photo-left': `${position.x}%`,
    '--photo-top': `${position.y}%`,
    '--photo-width': `${position.width}%`,
    '--photo-rotation': `${position.rotation}deg`,
    '--photo-order': index + 1,
});

const caption = (memory) => [memory.title, memory.description, memory.category, memory.date]
    .filter(Boolean)
    .join(' · ');

const updateImageDimensions = (event) => {
    const image = event.currentTarget;
    const link = image.closest('a[data-lightbox]');

    if (link) {
        link.dataset.pswpWidth = image.naturalWidth;
        link.dataset.pswpHeight = image.naturalHeight;
    }
};

const handlePointerMove = (event) => {
    if (isReducedMotion || isMobile || event.pointerType !== 'mouse' || !stage.value) {
        return;
    }

    const bounds = stage.value.getBoundingClientRect();
    const x = (event.clientX - bounds.left) / bounds.width - 0.5;
    const y = (event.clientY - bounds.top) / bounds.height - 0.5;

    stage.value.querySelectorAll('[data-cork-layer]').forEach((layer) => {
        const depth = Number(layer.dataset.depth);

        gsap.to(layer, {
            x: x * depth * -8,
            y: y * depth * -6,
            duration: 0.8,
            ease: 'power3.out',
            overwrite: true,
        });
    });
};

const resetParallax = () => {
    if (isReducedMotion || isMobile || !stage.value) {
        return;
    }

    gsap.to(stage.value.querySelectorAll('[data-cork-layer]'), {
        x: 0,
        y: 0,
        duration: 0.8,
        ease: 'power3.out',
        overwrite: true,
    });
};

onMounted(() => {
    isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    isMobile = window.matchMedia('(max-width: 767px)').matches;

    animationContext = gsap.context(() => {
        const photos = gsap.utils.toArray('[data-cork-photo]');
        const pins = gsap.utils.toArray('[data-cork-pin]');
        const threads = gsap.utils.toArray('[data-cork-thread]');

        if (!isReducedMotion && photos.length) {
            gsap.set(pins, { autoAlpha: 0, scale: 0.4, transformOrigin: 'center' });

            threads.forEach((thread) => {
                const length = thread.getTotalLength();
                gsap.set(thread, { strokeDasharray: length, strokeDashoffset: length });
            });

            const entrance = gsap.timeline({
                scrollTrigger: { trigger: stage.value, start: 'top 78%', once: true },
            });

            entrance
                .fromTo(photos, {
                    y: -72,
                    scale: 1.1,
                    rotation: (index) => laidOutMemories.value[index].position.rotation - 8,
                    autoAlpha: 0,
                }, {
                    y: 0,
                    scale: 1,
                    rotation: (index) => laidOutMemories.value[index].position.rotation,
                    autoAlpha: 1,
                    duration: 0.8,
                    stagger: 0.12,
                    ease: 'power3.out',
                })
                .to(pins, {
                    autoAlpha: 1,
                    scale: 1,
                    duration: 0.24,
                    stagger: 0.06,
                    ease: 'back.out(2)',
                }, '-=0.28')
                .to(threads, {
                    strokeDashoffset: 0,
                    duration: 0.65,
                    stagger: 0.08,
                    ease: 'power3.out',
                }, '-=0.2')
                .fromTo('[data-cork-stamp]', {
                    scale: 1.35,
                    autoAlpha: 0,
                    rotation: -8,
                }, {
                    scale: 1,
                    autoAlpha: 1,
                    rotation: -5,
                    duration: 0.38,
                    ease: 'back.out(2)',
                }, '-=0.45');
        }
    }, stage.value);

    lightbox = new PhotoSwipeLightbox({
        gallery: '#corkboard-archive',
        children: 'a[data-lightbox]',
        pswpModule: () => import('photoswipe'),
        bgOpacity: 0.94,
        wheelToZoom: true,
    });

    lightbox.on('uiRegister', () => {
        lightbox?.pswp?.ui.registerElement({
            name: 'corkboard-caption',
            order: 9,
            isButton: false,
            appendTo: 'root',
            html: '',
            onInit: (element) => {
                element.className = 'pswp__corkboard-caption';
                const updateCaption = () => {
                    element.textContent = lightbox?.pswp?.currSlide?.data.element?.dataset.caption || '';
                };

                updateCaption();
                lightbox?.pswp?.on('change', updateCaption);
            },
        });
    });

    lightbox.init();
});

onUnmounted(() => {
    animationContext?.revert();
    lightbox?.destroy();
});
</script>

<template>
    <div class="corkboard-archive">
        <div class="corkboard-frame">
            <div class="corkboard-lamp" aria-hidden="true"><span></span></div>
            <header class="corkboard-plaque">
                <svg class="corkboard-wood-grain" viewBox="0 0 1200 180" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M20 44c135-20 220 8 350-4s220-18 350-2 300 15 460-6M-20 132c160-16 255 12 400 0s260-15 390 1 250 13 450-2" />
                    <path d="m346 0 12 58-8 25 11 42M868 4l-9 39 12 27-8 47" />
                </svg>
                <p class="corkboard-plaque-label">{{ site.site_name || 'DATA BELUM TERSEDIA' }}</p>
                <h3 class="font-editorial">{{ site.cohort_name || 'DATA BELUM TERSEDIA' }}</h3>
                <p class="corkboard-plaque-year">{{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</p>
            </header>

            <div ref="stage" class="corkboard-stage" @pointermove="handlePointerMove" @pointerleave="resetParallax">
                <svg class="corkboard-textures" viewBox="0 0 1000 800" preserveAspectRatio="none" aria-hidden="true">
                    <filter id="cork-noise">
                        <feTurbulence type="fractalNoise" baseFrequency=".78" numOctaves="3" stitchTiles="stitch" />
                        <feColorMatrix type="saturate" values="0" />
                    </filter>
                    <rect width="100%" height="100%" filter="url(#cork-noise)" opacity=".15" />
                </svg>
                <span class="corkboard-vignette" aria-hidden="true"></span>
                <svg v-if="memories.length > 1" class="corkboard-threads" viewBox="0 0 1000 800" preserveAspectRatio="none" aria-hidden="true">
                    <path v-for="(path, index) in threadPaths" :key="index" :d="path" data-cork-thread />
                </svg>

                <div
                    v-for="(memory, index) in laidOutMemories"
                    :key="memory.id"
                    class="corkboard-photo-layer"
                    data-cork-layer
                    :data-depth="memory.position.depth"
                    :style="photoStyle(memory.position, index)"
                >
                    <a
                        data-lightbox
                        data-cork-photo
                        :href="memory.image_url"
                        data-pswp-width="1600"
                        data-pswp-height="1200"
                        :data-caption="caption(memory)"
                        :aria-label="`Buka foto ${memory.title}`"
                        class="corkboard-photo focus-ring"
                    >
                        <img :src="memory.image_url" :alt="memory.title" :loading="index < 2 ? 'eager' : 'lazy'" @load="updateImageDimensions">
                        <span class="corkboard-photo-caption">{{ memory.title }}</span>
                    </a>
                    <span class="corkboard-pin" data-cork-pin aria-hidden="true"></span>
                </div>

                <div v-if="!memories.length" class="corkboard-empty-frames" role="status">
                    <div v-for="frame in 4" :key="frame" class="corkboard-empty-frame">
                        <span class="corkboard-empty-pin" aria-hidden="true"></span>
                        <span class="corkboard-empty-copy">DATA BELUM TERSEDIA</span>
                    </div>
                    <p class="corkboard-empty-note">{{ site.footer_text || 'DATA BELUM TERSEDIA' }}</p>
                </div>

                <div class="corkboard-paper-tag" aria-hidden="true">
                    <span class="corkboard-tag-string"></span>
                    <span class="corkboard-tag-hole"></span>
                    <span>KENANGAN</span>
                </div>
                <div class="corkboard-stamp" data-cork-stamp>
                    <span>ARSIP</span>
                    <span>{{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</span>
                </div>
                <span class="corkboard-tape" aria-hidden="true"></span>
            </div>
        </div>

        <div class="corkboard-cta-wrap">
            <p class="corkboard-cta-note">{{ site.footer_text || 'DATA BELUM TERSEDIA' }}</p>
            <Link href="/kenangan" class="corkboard-cta focus-ring">
                LIHAT SEMUA KENANGAN <ArrowUpRight :size="15" />
            </Link>
        </div>
    </div>
</template>

<style>
.corkboard-archive {
    --paper: #f5f2ec;
    --pin: #8b4b45;
    color: var(--paper);
}

.corkboard-frame {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    border: 12px solid #34271f;
    background:
        linear-gradient(135deg, rgb(255 255 255 / 4%), transparent 32%),
        repeating-linear-gradient(7deg, transparent 0 13px, rgb(37 23 15 / 15%) 14px, transparent 16px 31px),
        radial-gradient(ellipse at 51% 42%, #795a3e, #634830 54%, #433124 100%);
    box-shadow: 0 28px 70px rgb(0 0 0 / 28%), inset 0 0 0 2px rgb(232 197 148 / 20%);
}

.corkboard-frame::before {
    position: absolute;
    z-index: -1;
    inset: 0;
    background:
        radial-gradient(ellipse at center, transparent 35%, rgb(15 10 8 / 52%) 100%),
        radial-gradient(ellipse at 50% 0%, rgb(240 185 105 / 16%), transparent 48%);
    content: '';
    pointer-events: none;
}

.corkboard-lamp {
    position: absolute;
    z-index: 4;
    top: 0;
    left: 50%;
    width: 1px;
    height: 48px;
    background: rgb(228 199 158 / 70%);
}

.corkboard-lamp::before {
    position: absolute;
    top: 43px;
    left: -16px;
    width: 32px;
    height: 14px;
    border-radius: 2px 2px 12px 12px;
    background: linear-gradient(#e1c18e, #8a6542);
    box-shadow: 0 0 18px 6px rgb(248 197 121 / 32%), 0 0 95px 42px rgb(235 169 86 / 17%);
    content: '';
}

.corkboard-lamp span {
    position: absolute;
    top: 56px;
    left: -120px;
    width: 240px;
    height: 360px;
    background: radial-gradient(ellipse at top, rgb(239 177 92 / 20%), transparent 72%);
    animation: corkboard-light 5s ease-in-out infinite;
    pointer-events: none;
}

.corkboard-plaque {
    position: relative;
    z-index: 2;
    display: grid;
    min-height: 145px;
    justify-items: center;
    align-content: center;
    gap: 5px;
    overflow: hidden;
    margin: 72px 8% 0;
    border: 1px solid rgb(246 223 187 / 42%);
    background:
        linear-gradient(180deg, rgb(239 210 166 / 18%), transparent 22%, rgb(45 29 18 / 28%)),
        repeating-linear-gradient(4deg, #553b27 0 5px, #61442c 6px 12px, #503721 13px 19px);
    box-shadow: inset 0 0 0 5px rgb(42 27 18 / 32%), 0 12px 22px rgb(0 0 0 / 28%);
    text-align: center;
}

.corkboard-wood-grain {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    fill: none;
    stroke: rgb(227 190 143 / 16%);
    stroke-width: 2;
}

.corkboard-plaque-label,
.corkboard-plaque-year {
    position: relative;
    color: #f1dfc4;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-shadow: 0 1px 2px rgb(0 0 0 / 80%);
}

.corkboard-plaque h3 {
    position: relative;
    max-width: 90%;
    color: #f5e8d4;
    font-size: clamp(1.5rem, 4vw, 3rem);
    line-height: 1;
    text-shadow: 0 2px 3px rgb(0 0 0 / 70%);
}

.corkboard-stage {
    position: relative;
    min-height: 660px;
    margin: 6px 22px 24px;
    overflow: hidden;
    border: 1px solid rgb(31 21 15 / 28%);
    background:
        radial-gradient(ellipse at 50% 42%, rgb(191 142 91 / 16%), transparent 65%),
        repeating-linear-gradient(33deg, transparent 0 18px, rgb(38 24 17 / 8%) 19px, transparent 21px 39px);
    box-shadow: inset 0 0 0 1px rgb(227 190 143 / 9%);
}

.corkboard-textures,
.corkboard-vignette,
.corkboard-threads {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.corkboard-textures {
    mix-blend-mode: soft-light;
}

.corkboard-vignette {
    background: radial-gradient(ellipse at center, transparent 34%, rgb(22 14 10 / 42%) 100%);
}

.corkboard-threads {
    z-index: 1;
    overflow: visible;
}

.corkboard-threads path {
    fill: none;
    stroke: #a3544d;
    stroke-linecap: round;
    stroke-width: 2.3;
    filter: drop-shadow(0 1px 1px rgb(0 0 0 / 55%));
}

.corkboard-photo-layer {
    position: absolute;
    z-index: var(--photo-order);
    top: var(--photo-top);
    left: var(--photo-left);
    width: var(--photo-width);
}

.corkboard-photo-layer:hover,
.corkboard-photo-layer:focus-within {
    z-index: 30;
}

.corkboard-photo {
    position: relative;
    display: block;
    border: 7px solid #f6f0e5;
    background: #f6f0e5;
    box-shadow: 0 9px 16px rgb(0 0 0 / 42%), 0 2px 4px rgb(0 0 0 / 40%);
    color: #241d17;
    transform: rotate(var(--photo-rotation));
    transition: filter 260ms ease, box-shadow 260ms ease, transform 260ms ease;
}

.corkboard-photo img {
    display: block;
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    filter: sepia(0.16) saturate(0.76);
    transition: filter 300ms ease;
}

.corkboard-photo-caption {
    display: block;
    overflow: hidden;
    padding: 8px 3px 1px;
    color: #302820;
    font: 600 10px/1.35 var(--font-sans);
    text-overflow: ellipsis;
    white-space: nowrap;
}

.corkboard-pin,
.corkboard-empty-pin {
    position: absolute;
    z-index: 2;
    top: -11px;
    left: 50%;
    width: 13px;
    height: 13px;
    border: 1px solid rgb(255 255 255 / 30%);
    border-radius: 50%;
    background: radial-gradient(circle at 35% 28%, #d2877b, var(--pin) 55%, #4a2825);
    box-shadow: 1px 2px 3px rgb(0 0 0 / 50%);
    transform: translateX(-50%);
}

.corkboard-paper-tag {
    position: absolute;
    z-index: 6;
    top: 28px;
    left: 22px;
    display: grid;
    width: 116px;
    min-height: 74px;
    place-items: center;
    padding-top: 8px;
    background: #e6d7b8;
    box-shadow: 2px 5px 10px rgb(0 0 0 / 25%);
    color: #382b20;
    font: 700 10px/1 var(--font-sans);
    letter-spacing: 0.15em;
    transform: rotate(-6deg);
}

.corkboard-tag-string {
    position: absolute;
    top: -32px;
    left: 50%;
    width: 1px;
    height: 40px;
    background: #b89769;
}

.corkboard-tag-hole {
    position: absolute;
    top: 7px;
    left: calc(50% - 3px);
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #7c5b39;
}

.corkboard-stamp {
    position: absolute;
    z-index: 5;
    right: 25px;
    bottom: 24px;
    display: grid;
    gap: 3px;
    border: 2px solid rgb(139 75 69 / 78%);
    padding: 7px 10px;
    color: #e5b8a8;
    font: 700 10px/1.2 var(--font-sans);
    letter-spacing: 0.16em;
    text-align: center;
    transform: rotate(-5deg);
}

.corkboard-stamp span:last-child {
    font-size: 8px;
}

.corkboard-tape {
    position: absolute;
    z-index: 3;
    top: 5px;
    right: 23%;
    width: 62px;
    height: 20px;
    background: rgb(224 203 163 / 68%);
    box-shadow: 0 1px 3px rgb(0 0 0 / 20%);
    transform: rotate(8deg);
}

.corkboard-empty-frames {
    position: absolute;
    inset: 9% 8% 8%;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    align-content: center;
    gap: 28px 3%;
}

.corkboard-empty-frame {
    position: relative;
    display: grid;
    min-height: 170px;
    place-items: center;
    border: 7px solid #f6f0e5;
    background: linear-gradient(145deg, rgb(247 232 207 / 8%), rgb(31 23 18 / 30%));
    box-shadow: 0 8px 16px rgb(0 0 0 / 32%);
    transform: rotate(var(--empty-rotation, -2deg));
}

.corkboard-empty-frame:nth-child(2) {
    --empty-rotation: 3deg;
    transform: translateY(18px) rotate(3deg);
}

.corkboard-empty-frame:nth-child(3) {
    --empty-rotation: -4deg;
    transform: rotate(-4deg);
}

.corkboard-empty-frame:nth-child(4) {
    --empty-rotation: 2deg;
    transform: translateY(14px) rotate(2deg);
}

.corkboard-empty-copy {
    padding: 8px;
    color: #eee1cf;
    font: 600 9px/1.5 var(--font-sans);
    letter-spacing: 0.12em;
    text-align: center;
}

.corkboard-empty-pin {
    top: -12px;
}

.corkboard-empty-note {
    grid-column: 1 / -1;
    justify-self: center;
    padding: 8px 14px;
    background: rgb(35 25 19 / 75%);
    color: #f1e6d7;
    font: 500 11px/1.5 var(--font-sans);
    text-align: center;
}

.corkboard-cta-wrap {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 4px 4px;
}

.corkboard-cta-note {
    color: #55514b;
    font: 400 13px/1.6 var(--font-sans);
}

.corkboard-cta {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #987344;
    color: #171717;
    font: 700 10px/1 var(--font-sans);
    letter-spacing: 0.12em;
    transition: color 180ms ease, border-color 180ms ease;
}

.corkboard-cta:hover {
    border-color: #73552f;
    color: #73552f;
}

.corkboard-photo:hover,
.corkboard-photo:focus-visible {
    z-index: 20;
    box-shadow: 0 20px 35px rgb(0 0 0 / 55%), 0 4px 8px rgb(0 0 0 / 38%);
    filter: saturate(1.18);
    transform: rotate(calc(var(--photo-rotation) * 0.35)) scale(1.04);
}

.corkboard-photo:hover img,
.corkboard-photo:focus-visible img {
    filter: none;
}

.pswp__corkboard-caption {
    position: absolute;
    inset-inline: 0;
    bottom: 24px;
    padding-inline: 20px;
    color: white;
    font: 12px/1.6 var(--font-sans);
    text-align: center;
}

@keyframes corkboard-light {
    0%, 100% { opacity: 0.88; }
    48% { opacity: 1; }
    52% { opacity: 0.94; }
}

@media (max-width: 1023px) {
    .corkboard-frame {
        border-width: 9px;
    }

    .corkboard-plaque {
        margin-inline: 6%;
    }

    .corkboard-stage {
        min-height: 580px;
        margin-inline: 14px;
    }

    .corkboard-photo-layer {
        width: calc(var(--photo-width) * 1.12);
    }
}

@media (max-width: 639px) {
    .corkboard-frame {
        border-width: 6px;
    }

    .corkboard-lamp {
        height: 34px;
    }

    .corkboard-lamp::before {
        top: 30px;
        left: -12px;
        width: 24px;
        height: 11px;
    }

    .corkboard-lamp span {
        top: 40px;
        left: -80px;
        width: 160px;
        height: 220px;
    }

    .corkboard-plaque {
        min-height: 106px;
        gap: 4px;
        margin: 55px 7% 0;
    }

    .corkboard-plaque-label,
    .corkboard-plaque-year {
        font-size: 8px;
        letter-spacing: 0.11em;
    }

    .corkboard-plaque h3 {
        font-size: clamp(1.25rem, 7vw, 2rem);
    }

    .corkboard-stage {
        display: grid;
        min-height: 0;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-content: start;
        gap: 26px 14px;
        overflow: hidden;
        margin: 8px 7px 10px;
        padding: 76px 15px 96px;
    }

    .corkboard-photo-layer {
        position: relative;
        z-index: auto;
        top: auto;
        left: auto;
        width: 100%;
        transform: none !important;
    }

    .corkboard-photo {
        border-width: 5px;
        transform: rotate(calc(var(--photo-rotation) * 0.5));
    }

    .corkboard-photo-caption {
        padding-top: 6px;
        font-size: 9px;
    }

    .corkboard-pin {
        top: -9px;
        width: 11px;
        height: 11px;
    }

    .corkboard-threads {
        display: none;
    }

    .corkboard-paper-tag {
        top: 18px;
        left: 13px;
        width: 93px;
        min-height: 58px;
        font-size: 8px;
    }

    .corkboard-tag-string {
        top: -25px;
        height: 33px;
    }

    .corkboard-tag-hole {
        top: 5px;
    }

    .corkboard-stamp {
        right: 12px;
        bottom: 13px;
        padding: 6px 8px;
        font-size: 8px;
    }

    .corkboard-tape {
        top: 3px;
        right: 19%;
        width: 44px;
        height: 15px;
    }

    .corkboard-empty-frames {
        position: relative;
        inset: auto;
        display: grid;
        grid-column: 1 / -1;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 14px;
    }

    .corkboard-empty-frame {
        min-height: 125px;
        border-width: 5px;
    }

    .corkboard-empty-copy {
        font-size: 8px;
    }

    .corkboard-empty-note {
        font-size: 10px;
    }

    .corkboard-cta-wrap {
        padding-top: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .corkboard-lamp span {
        animation: none;
    }

    .corkboard-photo,
    .corkboard-photo img,
    .corkboard-cta {
        transition: none;
    }
}
</style>
