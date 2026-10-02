<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import { ArrowUpRight } from '@lucide/vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import 'photoswipe/style.css';
import RedThread from './RedThread.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    memories: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
    count: { type: Number, default: null },
    quote: { type: Object, default: null },
});

const stage = ref(null);
const paperPin = ref(null);
const photoRefs = new Map();
const pinRefs = new Map();
const points = ref([]);
const stageSize = ref({ width: 0, height: 0 });
const activeId = ref('');
const displayCount = ref(0);
const motionPreference = ref(false);
const mobileLayout = ref(false);
let animationContext;
let lightbox;
let resizeObserver;
let intersectionObserver;
let mediaQuery;

const photoLayouts = [
    { left: 4, top: 9, width: 22, rotation: -4, depth: 0.23 },
    { left: 30, top: 4, width: 20, rotation: 3, depth: 0.52 },
    { left: 72, top: 9, width: 21, rotation: -2, depth: 0.35 },
    { left: 1, top: 51, width: 22, rotation: 4, depth: 0.68 },
    { left: 74, top: 52, width: 21, rotation: -5, depth: 0.42 },
    { left: 30, top: 67, width: 20, rotation: 2, depth: 0.75 },
];
const emptyFrames = [
    { id: 'empty-1', left: 4, top: 10, width: 22, rotation: -3 },
    { id: 'empty-2', left: 72, top: 10, width: 21, rotation: 3 },
    { id: 'empty-3', left: 2, top: 55, width: 22, rotation: 2 },
    { id: 'empty-4', left: 74, top: 54, width: 21, rotation: -4 },
];

const boardPhotos = computed(() => props.memories.map((memory, index) => ({
    ...memory,
    boardId: `memory-${memory.id}`,
    position: photoLayouts[index % photoLayouts.length],
})));

const boardFrames = computed(() => props.memories.length
    ? boardPhotos.value
    : emptyFrames.map((frame) => ({ ...frame, boardId: frame.id })));

const cohortLabel = computed(() => props.site.cohort_name || 'DATA BELUM TERSEDIA');
const tapeLabels = computed(() => Array.from({ length: 8 }, (_, index) => `${cohortLabel.value}  ·  `));
const boardConnections = computed(() => {
    const ids = boardFrames.value.map((frame) => frame.boardId);

    if (!ids.length) {
        return [];
    }

    const connections = ids.slice(0, mobileLayout.value ? 2 : 4).map((id) => ['hub', id]);

    for (let index = 0; index < ids.length - 1; index += 1) {
        if (mobileLayout.value && index > 0) {
            break;
        }

        connections.push([ids[index], ids[index + 1]]);
    }

    return connections;
});

const positionStyle = (frame) => ({
    '--photo-left': `${frame.position?.left ?? frame.left}%`,
    '--photo-top': `${frame.position?.top ?? frame.top}%`,
    '--photo-width': `${frame.position?.width ?? frame.width}%`,
    '--photo-rotation': `${frame.position?.rotation ?? frame.rotation}deg`,
    '--photo-depth': frame.position?.depth ?? 0.5,
});

const caption = (memory) => [memory.title, memory.description, memory.category, memory.date]
    .filter(Boolean)
    .join(' · ');

const setPhotoRef = (id, element) => {
    if (element) {
        photoRefs.set(id, element);
    } else {
        photoRefs.delete(id);
    }
};

const setPinRef = (id, element) => {
    if (element) {
        pinRefs.set(id, element);
    } else {
        pinRefs.delete(id);
    }
};

const refreshPoints = () => {
    if (!stage.value) {
        return;
    }

    const bounds = stage.value.getBoundingClientRect();
    stageSize.value = { width: bounds.width, height: bounds.height };

    const currentPoints = [];
    const hub = paperPin.value?.getBoundingClientRect();

    if (hub) {
        currentPoints.push({
            id: 'hub',
            x: hub.left + hub.width / 2 - bounds.left,
            y: hub.top + hub.height / 2 - bounds.top,
        });
    }

    boardFrames.value.forEach((frame) => {
        const pin = pinRefs.get(frame.boardId)?.getBoundingClientRect();

        if (pin) {
            currentPoints.push({
                id: frame.boardId,
                x: pin.left + pin.width / 2 - bounds.left,
                y: pin.top + pin.height / 2 - bounds.top,
            });
        }
    });

    points.value = currentPoints;
};

const updateImageDimensions = (event) => {
    const image = event.currentTarget;
    const link = image.closest('a[data-lightbox]');

    if (link) {
        link.dataset.pswpWidth = image.naturalWidth;
        link.dataset.pswpHeight = image.naturalHeight;
    }

    refreshPoints();
};

const activatePhoto = (id) => {
    activeId.value = id;
};

const deactivatePhoto = (id) => {
    if (activeId.value === id) {
        activeId.value = '';
    }
};

const handlePhotoClick = (event, id) => {
    if (mobileLayout.value && event.pointerType !== 'mouse' && activeId.value !== id) {
        event.preventDefault();
        activatePhoto(id);
    }
};

const handlePointerMove = (event) => {
    if (motionPreference.value || mobileLayout.value || event.pointerType !== 'mouse' || !stage.value) {
        return;
    }

    const bounds = stage.value.getBoundingClientRect();
    const x = (event.clientX - bounds.left) / bounds.width - 0.5;
    const y = (event.clientY - bounds.top) / bounds.height - 0.5;

    gsap.to(stage.value.querySelectorAll('[data-noir-background]'), {
        x: x * -4,
        y: y * -3,
        duration: 0.9,
        ease: 'power3.out',
        overwrite: true,
    });
};

const resetParallax = () => {
    if (!stage.value || motionPreference.value || mobileLayout.value) {
        return;
    }

    gsap.to(stage.value.querySelectorAll('[data-noir-background]'), {
        x: 0,
        y: 0,
        duration: 0.9,
        ease: 'power3.out',
        overwrite: true,
    });
};

const observeMobileCenter = () => {
    intersectionObserver?.disconnect();
    intersectionObserver = null;

    if (!mobileLayout.value || !stage.value) {
        return;
    }

    intersectionObserver = new IntersectionObserver((entries) => {
        const centered = entries
            .filter((entry) => entry.isIntersecting)
            .sort((first, second) => second.intersectionRatio - first.intersectionRatio)[0];

        if (centered) {
            activeId.value = centered.target.dataset.memoryId || '';
        }
    }, {
        rootMargin: '-42% 0px -42% 0px',
        threshold: [0, 0.2, 0.5, 0.8, 1],
    });

    stage.value.querySelectorAll('[data-memory-photo]').forEach((photo) => intersectionObserver.observe(photo));
};

const updateMediaState = () => {
    motionPreference.value = mediaQuery.matches;
    mobileLayout.value = window.matchMedia('(max-width: 639px), (hover: none)').matches;
    observeMobileCenter();
};

const animateEntrance = () => {
    if (motionPreference.value || !stage.value) {
        return;
    }

    animationContext = gsap.context(() => {
        const photos = gsap.utils.toArray('[data-noir-photo]');
        const labels = gsap.utils.toArray('[data-noir-label]');
        const pins = gsap.utils.toArray('[data-noir-pin]');
        const threadPaths = gsap.utils.toArray('.red-thread__line, .red-thread__fiber');
        const number = stage.value.querySelector('[data-memory-count]');

        threadPaths.forEach((path) => {
            const length = path.getTotalLength();
            gsap.set(path, { strokeDasharray: length, strokeDashoffset: length });
        });

        const timeline = gsap.timeline({
            scrollTrigger: { trigger: stage.value, start: 'top 78%', once: true },
        });

        timeline
            .fromTo('[data-noir-background]', { autoAlpha: 0.2 }, { autoAlpha: 1, duration: 0.8 })
            .fromTo(photos, {
                y: -26,
                scale: 1.15,
                rotation: (index) => (boardPhotos.value[index]?.position.rotation ?? emptyFrames[index]?.rotation ?? 0) - 5,
                autoAlpha: 0,
            }, {
                y: 0,
                scale: 1,
                rotation: (index) => boardPhotos.value[index]?.position.rotation ?? emptyFrames[index]?.rotation ?? 0,
                autoAlpha: 1,
                duration: 0.65,
                stagger: 0.1,
                ease: 'power3.out',
            }, '-=0.25')
            .fromTo(labels, { clipPath: 'inset(0 100% 0 0)' }, {
                clipPath: 'inset(0 0% 0 0)',
                duration: 0.38,
                stagger: 0.06,
            }, '-=0.2')
            .fromTo(pins, { scale: 0.4, autoAlpha: 0 }, {
                scale: 1,
                autoAlpha: 1,
                duration: 0.2,
                stagger: 0.04,
                ease: 'back.out(2)',
            }, '-=0.18')
            .to(threadPaths, {
                strokeDashoffset: 0,
                duration: 0.9,
                stagger: 0.08,
                ease: 'power2.inOut',
            }, '-=0.1');

        if (number && props.count !== null) {
            const countTween = { value: 0 };

            timeline.fromTo(countTween, { value: 0 }, {
                value: props.count,
                duration: 0.85,
                ease: 'power2.out',
                onUpdate: () => {
                    number.textContent = `${Math.round(countTween.value)}`;
                },
            }, '-=0.25');
        }

        if (!mobileLayout.value) {
            gsap.utils.toArray('.red-thread__group').forEach((thread, index) => {
                gsap.to(thread, {
                    y: index % 2 === 0 ? -2 : 2,
                    duration: 4.5 + (index % 3) * 0.5,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                    delay: (index * 0.37) % 1.8,
                });
            });
        }
    }, stage.value);
};

onMounted(async () => {
    mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    updateMediaState();
    await nextTick();
    refreshPoints();
    observeMobileCenter();
    await nextTick();

    resizeObserver = new ResizeObserver(refreshPoints);
    if (stage.value) {
        resizeObserver.observe(stage.value);
    }
    photoRefs.forEach((element) => resizeObserver.observe(element));

    animateEntrance();

    if (stage.value) {
        lightbox = new PhotoSwipeLightbox({
            gallery: stage.value,
            children: 'a[data-lightbox]',
            pswpModule: () => import('photoswipe'),
            bgOpacity: 0.96,
            wheelToZoom: true,
        });

        lightbox.on('uiRegister', () => {
            lightbox?.pswp?.ui.registerElement({
                name: 'noir-caption',
                order: 9,
                isButton: false,
                appendTo: 'root',
                html: '',
                onInit: (element) => {
                    element.className = 'pswp__noir-caption';
                    const updateCaption = () => {
                        element.textContent = lightbox?.pswp?.currSlide?.data.element?.dataset.caption || '';
                    };

                    updateCaption();
                    lightbox?.pswp?.on('change', updateCaption);
                },
            });
        });
        lightbox.init();
    }

    mediaQuery.addEventListener('change', updateMediaState);
});

onUnmounted(() => {
    mediaQuery?.removeEventListener('change', updateMediaState);
    intersectionObserver?.disconnect();
    resizeObserver?.disconnect();
    animationContext?.revert();
    lightbox?.destroy();
});
</script>

<template>
    <div class="noir-archive">
        <div
            ref="stage"
            class="noir-board"
            :class="{ 'noir-board--has-active': activeId }"
            :style="{ '--board-width': `${stageSize.width}px`, '--board-height': `${stageSize.height}px` }"
            @pointermove="handlePointerMove"
            @pointerleave="resetParallax"
        >
            <div class="noir-newspaper" data-noir-background aria-hidden="true">
                <div class="noir-paper-sheet noir-paper-sheet--one">
                    <span class="noir-paper-headline"></span>
                    <span class="noir-paper-columns"></span>
                    <span class="noir-paper-columns noir-paper-columns--short"></span>
                </div>
                <div class="noir-paper-sheet noir-paper-sheet--two">
                    <span class="noir-paper-headline noir-paper-headline--small"></span>
                    <span class="noir-paper-columns"></span>
                </div>
                <span class="noir-halftone"></span>
                <svg class="noir-grain" viewBox="0 0 400 300" preserveAspectRatio="none">
                    <filter id="noir-film-grain">
                        <feTurbulence type="fractalNoise" baseFrequency=".92" numOctaves="2" stitchTiles="stitch" />
                        <feColorMatrix type="saturate" values="0" />
                    </filter>
                    <rect width="100%" height="100%" filter="url(#noir-film-grain)" opacity=".14" />
                </svg>
                <span class="noir-vignette"></span>
            </div>

            <div class="noir-red-tape" aria-hidden="true">
                <div class="noir-red-tape__track">
                    <span v-for="(label, index) in tapeLabels" :key="index">{{ label }}</span>
                </div>
            </div>

            <RedThread
                :points="points"
                :connections="boardConnections"
                :width="stageSize.width"
                :height="stageSize.height"
                :reduced-motion="motionPreference"
                :is-mobile="mobileLayout"
                :active-id="activeId"
            />

            <article class="noir-central-paper" data-noir-paper>
                <span ref="paperPin" class="noir-central-pin" aria-hidden="true"></span>
                <p v-if="count !== null" data-memory-count class="noir-central-count font-editorial">{{ count }}</p>
                <p v-else class="noir-central-count noir-central-count--empty font-editorial">DATA BELUM TERSEDIA</p>
                <p class="noir-central-label">KENANGAN</p>
                <div v-if="quote?.content" class="noir-sticky-note">
                    <span class="noir-sticky-pin" aria-hidden="true"></span>
                    <p>{{ quote.content }}</p>
                    <span v-if="quote.attribution" class="noir-sticky-attribution">{{ quote.attribution }}</span>
                </div>
            </article>

            <article
                v-for="(frame, index) in boardFrames"
                :key="frame.boardId"
                :ref="(element) => setPhotoRef(frame.boardId, element)"
                class="noir-photo-position"
                :style="positionStyle(frame)"
                :data-depth="frame.position?.depth ?? 0.5"
            >
                <a
                    v-if="memories.length"
                    :id="frame.boardId"
                    data-lightbox
                    data-noir-photo
                    data-memory-photo
                    :data-memory-id="frame.boardId"
                    :href="frame.image_url"
                    data-pswp-width="1600"
                    data-pswp-height="1200"
                    :data-caption="caption(frame)"
                    :aria-label="`Buka foto ${frame.title}`"
                    class="noir-polaroid focus-ring"
                    :class="{ 'is-active': activeId === frame.boardId }"
                    @mouseenter="activatePhoto(frame.boardId)"
                    @mouseleave="deactivatePhoto(frame.boardId)"
                    @focusin="activatePhoto(frame.boardId)"
                    @focusout="deactivatePhoto(frame.boardId)"
                    @click="handlePhotoClick($event, frame.boardId)"
                >
                    <span :ref="(element) => setPinRef(frame.boardId, element)" class="noir-photo-pin" data-noir-pin aria-hidden="true"></span>
                    <span class="noir-photo-image-wrap">
                        <img :src="frame.image_url" :alt="frame.title" :loading="index < 2 ? 'eager' : 'lazy'" @load="updateImageDimensions">
                        <span class="noir-photo-halftone" aria-hidden="true"></span>
                    </span>
                    <span class="noir-photo-caption">{{ frame.title }}</span>
                    <span class="noir-photo-category" data-noir-label>{{ frame.category?.toUpperCase() || 'DATA BELUM TERSEDIA' }}</span>
                </a>
                <div v-else class="noir-polaroid noir-polaroid--empty" data-noir-photo :aria-hidden="index > 0 ? 'true' : undefined" :role="index === 0 ? 'status' : undefined" :aria-label="index === 0 ? 'Foto kenangan belum tersedia' : undefined">
                    <span :ref="(element) => setPinRef(frame.boardId, element)" class="noir-photo-pin" data-noir-pin aria-hidden="true"></span>
                    <span class="noir-empty-image">
                        <span class="noir-empty-copy">DATA BELUM TERSEDIA</span>
                    </span>
                    <span class="noir-photo-caption">KENANGAN</span>
                    <span class="noir-photo-category" data-noir-label>DATA BELUM TERSEDIA</span>
                </div>
            </article>
        </div>

        <div class="noir-archive-footer">
            <p>{{ site.footer_text || 'DATA BELUM TERSEDIA' }}</p>
            <Link href="/kenangan" class="noir-archive-cta focus-ring">
                LIHAT SEMUA KENANGAN <ArrowUpRight :size="15" />
            </Link>
        </div>
    </div>
</template>

<style>
.noir-archive {
    --noir-black: #111;
    --noir-charcoal: #242424;
    --noir-paper: #f5f2ec;
    --noir-red: #8b4b45;
    --noir-thread: #a8433b;
    color: var(--noir-paper);
}

.noir-board {
    position: relative;
    isolation: isolate;
    min-height: 780px;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 47% 42%, #302e2c 0%, #242424 48%, #111 100%);
    box-shadow: inset 0 0 0 1px rgb(245 242 236 / 14%), 0 24px 55px rgb(0 0 0 / 25%);
}

.noir-newspaper {
    position: absolute;
    z-index: 0;
    inset: -8%;
    overflow: hidden;
    opacity: 0.52;
    transform: rotate(-1.6deg) scale(1.06);
}

.noir-paper-sheet {
    position: absolute;
    width: 68%;
    height: 47%;
    border: 1px solid rgb(220 216 208 / 14%);
    background:
        linear-gradient(90deg, transparent 32%, rgb(0 0 0 / 14%) 33%, transparent 34%, transparent 66%, rgb(0 0 0 / 12%) 67%, transparent 68%),
        #373635;
    box-shadow: 0 14px 35px rgb(0 0 0 / 32%);
    opacity: 0.7;
}

.noir-paper-sheet--one {
    top: 7%;
    left: -4%;
    transform: rotate(-8deg);
}

.noir-paper-sheet--two {
    right: -6%;
    bottom: 1%;
    transform: rotate(7deg);
}

.noir-paper-headline {
    position: absolute;
    top: 10%;
    left: 8%;
    width: 70%;
    height: 12%;
    background: #c2bfba;
    opacity: 0.2;
}

.noir-paper-headline--small {
    width: 48%;
    height: 9%;
}

.noir-paper-columns {
    position: absolute;
    top: 29%;
    right: 8%;
    bottom: 10%;
    left: 8%;
    background: repeating-linear-gradient(to bottom, rgb(226 223 218 / 27%) 0 1px, transparent 1px 7px);
    mask-image: linear-gradient(90deg, #000 0 29%, transparent 29% 33%, #000 33% 63%, transparent 63% 68%, #000 68% 100%);
}

.noir-paper-columns--short {
    right: 22%;
    bottom: 20%;
    left: 40%;
}

.noir-halftone {
    position: absolute;
    inset: 4% 12% 9%;
    background-image: radial-gradient(rgb(237 232 224 / 24%) 0.7px, transparent 0.8px);
    background-size: 5px 5px;
    opacity: 0.18;
    mask-image: radial-gradient(ellipse at 75% 40%, #000, transparent 70%);
}

.noir-grain,
.noir-vignette {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.noir-grain {
    mix-blend-mode: soft-light;
    opacity: 0.3;
}

.noir-vignette {
    background: radial-gradient(ellipse at center, transparent 34%, rgb(0 0 0 / 62%) 100%);
}

.noir-red-tape {
    position: absolute;
    z-index: 1;
    top: 44%;
    left: -15%;
    display: flex;
    width: 130%;
    height: 45px;
    overflow: hidden;
    align-items: center;
    background: rgb(139 75 69 / 86%);
    box-shadow: 0 5px 18px rgb(0 0 0 / 30%);
    transform: rotate(-7deg);
}

.noir-red-tape__track {
    display: flex;
    width: max-content;
    gap: 16px;
    white-space: nowrap;
}

.noir-red-tape__track span {
    color: #fff6ed;
    font: 700 10px/1 Inter, var(--font-sans);
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.red-thread {
    z-index: 4;
}

.noir-central-paper {
    position: absolute;
    z-index: 3;
    top: 37%;
    left: 41%;
    display: grid;
    width: 22%;
    min-height: 170px;
    justify-items: center;
    align-content: center;
    padding: 20px 12px 14px;
    background:
        linear-gradient(135deg, rgb(255 255 255 / 14%), transparent 38%),
        #e6e0d5;
    box-shadow: 0 13px 27px rgb(0 0 0 / 43%);
    color: #181716;
    text-align: center;
    transform: rotate(-1.5deg);
}

.noir-central-pin,
.noir-photo-pin,
.noir-sticky-pin {
    position: absolute;
    z-index: 6;
    top: -8px;
    left: 50%;
    width: 13px;
    height: 13px;
    border: 1px solid rgb(255 255 255 / 26%);
    border-radius: 50%;
    background: radial-gradient(circle at 33% 27%, #e27c70, var(--noir-thread) 52%, #51241f);
    box-shadow: 1px 2px 4px rgb(0 0 0 / 55%);
    transform: translateX(-50%);
}

.noir-central-count {
    max-width: 100%;
    overflow-wrap: anywhere;
    color: #a8433b;
    font-size: clamp(1.75rem, 4vw, 4rem);
    font-weight: 700;
    line-height: 0.95;
}

.noir-central-count--empty {
    font-size: clamp(1rem, 2vw, 1.45rem);
    line-height: 1.15;
}

.noir-central-label {
    margin-top: 7px;
    color: #302c29;
    font: 700 9px/1.2 Inter, var(--font-sans);
    letter-spacing: 0.22em;
}

.noir-sticky-note {
    position: absolute;
    z-index: 7;
    top: calc(100% + 22px);
    right: -24px;
    display: grid;
    width: min(180px, 88%);
    min-height: 88px;
    align-content: center;
    gap: 6px;
    padding: 17px 12px 12px;
    background: #ded5b8;
    box-shadow: 3px 7px 14px rgb(0 0 0 / 28%);
    color: #2e2a24;
    font: 500 11px/1.5 var(--font-sans);
    text-align: left;
    transform: rotate(4deg);
}

.noir-sticky-pin {
    top: -6px;
    width: 10px;
    height: 10px;
}

.noir-sticky-attribution {
    color: #51483d;
    font: 600 9px/1.4 var(--font-sans);
    text-align: right;
}

.noir-photo-position {
    position: absolute;
    z-index: 2;
    top: var(--photo-top);
    left: var(--photo-left);
    width: var(--photo-width);
}

.noir-photo-position:hover,
.noir-photo-position:focus-within {
    z-index: 8;
}

.noir-polaroid {
    position: relative;
    display: block;
    border: 7px solid var(--noir-paper);
    border-bottom-width: 27px;
    background: var(--noir-paper);
    box-shadow: 0 9px 20px rgb(0 0 0 / 48%), 0 2px 5px rgb(0 0 0 / 40%);
    color: #201c19;
    cursor: zoom-in;
    transform: rotate(var(--photo-rotation));
    transition: box-shadow 260ms ease, transform 260ms ease, opacity 260ms ease;
}

.noir-photo-image-wrap {
    position: relative;
    display: block;
    overflow: hidden;
    aspect-ratio: 4 / 3;
    background: #353433;
}

.noir-photo-image-wrap img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(1) contrast(1.1) brightness(0.9);
    transition: filter 560ms ease-out, opacity 260ms ease;
}

.noir-photo-halftone {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgb(0 0 0 / 50%) 0.7px, transparent 0.85px);
    background-size: 5px 5px;
    opacity: 0.16;
    pointer-events: none;
    transition: opacity 300ms ease;
}

.noir-photo-caption {
    position: absolute;
    right: 3px;
    bottom: -21px;
    left: 3px;
    overflow: hidden;
    color: #302b26;
    font: 600 9px/1.2 var(--font-sans);
    text-overflow: ellipsis;
    white-space: nowrap;
}

.noir-photo-category {
    position: absolute;
    z-index: 2;
    bottom: 6px;
    left: 6px;
    max-width: calc(100% - 12px);
    overflow: hidden;
    background: #8b3e38;
    padding: 5px 7px 4px;
    color: #fff;
    font: 700 8px/1.2 Inter, var(--font-sans);
    letter-spacing: 0.12em;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.noir-polaroid:hover,
.noir-polaroid:focus-visible,
.noir-polaroid.is-active {
    box-shadow: 0 19px 34px rgb(0 0 0 / 62%), 0 5px 9px rgb(0 0 0 / 42%);
    transform: rotate(calc(var(--photo-rotation) * 0.25)) scale(1.04);
}

.noir-polaroid:hover img,
.noir-polaroid:focus-visible img,
.noir-polaroid.is-active img {
    filter: grayscale(0) contrast(1) brightness(1);
}

.noir-polaroid:hover .noir-photo-halftone,
.noir-polaroid:focus-visible .noir-photo-halftone,
.noir-polaroid.is-active .noir-photo-halftone {
    opacity: 0;
}

.noir-board--has-active .noir-photo-position:not(:has(.is-active)) .noir-polaroid img {
    opacity: 0.75;
}

.noir-polaroid--empty {
    cursor: default;
}

.noir-empty-image {
    display: grid;
    aspect-ratio: 4 / 3;
    place-items: center;
    border: 1px solid rgb(245 242 236 / 19%);
    background:
        linear-gradient(140deg, transparent 48%, rgb(245 242 236 / 12%) 49% 50%, transparent 51%),
        repeating-linear-gradient(0deg, #2e2d2c 0 2px, #353433 2px 4px);
}

.noir-empty-copy {
    padding: 8px;
    color: #fff;
    font: 700 8px/1.5 Inter, var(--font-sans);
    letter-spacing: 0.12em;
    text-align: center;
}

.noir-archive-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 2px 0;
}

.noir-archive-footer > p {
    max-width: 42rem;
    color: #d7d1c7;
    font: 400 12px/1.6 var(--font-sans);
}

.noir-archive-cta {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #8b4b45;
    color: #f5f2ec;
    font: 700 10px/1 Inter, var(--font-sans);
    letter-spacing: 0.12em;
    transition: color 180ms ease, border-color 180ms ease;
}

.noir-archive-cta:hover {
    border-color: #d17b71;
    color: #f4c7c0;
}

.pswp__noir-caption {
    position: absolute;
    inset-inline: 0;
    bottom: 24px;
    padding-inline: 20px;
    color: #fff;
    font: 12px/1.6 var(--font-sans);
    text-align: center;
}

@media (hover: hover) and (pointer: fine) {
    .noir-board--has-active .noir-photo-position:not(:has(.is-active)) .noir-polaroid img {
        opacity: 0.75;
    }
}

@media (max-width: 1023px) {
    .noir-board {
        min-height: 690px;
    }

    .noir-photo-position {
        width: calc(var(--photo-width) * 1.1);
    }

    .noir-central-paper {
        min-height: 145px;
    }
}

@media (max-width: 639px) {
    .noir-board {
        display: grid;
        min-height: 0;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-content: start;
        gap: 24px 18px;
        padding: 94px 18px 34px;
    }

    .noir-newspaper {
        inset: -4%;
    }

    .noir-paper-sheet {
        width: 110%;
        height: 35%;
    }

    .noir-paper-sheet--one {
        top: 2%;
        left: -10%;
    }

    .noir-paper-sheet--two {
        right: -25%;
        bottom: 3%;
    }

    .noir-red-tape {
        top: 46%;
        height: 34px;
        transform: rotate(-9deg);
    }

    .noir-red-tape__track span {
        font-size: 8px;
    }

    .noir-central-paper {
        position: relative;
        z-index: 5;
        top: auto;
        left: auto;
        grid-column: 1 / -1;
        grid-row: 1;
        width: min(100%, 260px);
        min-height: 105px;
        justify-self: center;
        padding: 14px 12px 10px;
        transform: rotate(-1deg);
    }

    .noir-central-count {
        font-size: clamp(2rem, 12vw, 3rem);
    }

    .noir-sticky-note {
        top: 20%;
        right: -68px;
        width: 132px;
        min-height: 70px;
        padding: 12px 8px 8px;
        font-size: 9px;
    }

    .noir-photo-position {
        position: relative;
        z-index: 2;
        top: auto;
        left: auto;
        width: 100%;
    }

    .noir-polaroid {
        border-width: 5px;
        border-bottom-width: 21px;
        transform: rotate(calc(var(--photo-rotation) * 0.45));
        transition: box-shadow 260ms ease, opacity 260ms ease;
    }

    .noir-polaroid:hover,
    .noir-polaroid:focus-visible,
    .noir-polaroid.is-active {
        transform: none;
    }

    .noir-photo-caption {
        bottom: -17px;
        font-size: 8px;
    }

    .noir-photo-category {
        bottom: 5px;
        left: 5px;
        padding: 4px 5px 3px;
        font-size: 7px;
    }

    .noir-board--has-active .noir-photo-position:not(:has(.is-active)) .noir-polaroid img {
        opacity: 1;
    }

    .noir-empty-copy {
        font-size: 7px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .noir-polaroid,
    .noir-photo-image-wrap img,
    .noir-photo-halftone,
    .noir-archive-cta {
        transition: none;
    }

    .noir-polaroid:hover,
    .noir-polaroid:focus-visible,
    .noir-polaroid.is-active {
        transform: none;
    }
}
</style>
