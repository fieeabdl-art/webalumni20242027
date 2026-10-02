<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import gsap from 'gsap';

const cursor = ref(null);
let moveX;
let moveY;
let scaleCursor;
let showCursor;
let magneticX;
let magneticY;
let magneticTarget;
let isEnabled = false;
const magneticTargets = new Set();

const resetMagneticTarget = () => {
    magneticX?.(0);
    magneticY?.(0);
    magneticTarget = null;
    magneticX = null;
    magneticY = null;
};

const handlePointerMove = (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const interactiveTarget = target?.closest('a, button, [data-cursor-interactive]');

    moveX?.(event.clientX - 12);
    moveY?.(event.clientY - 12);
    scaleCursor?.(interactiveTarget ? 1.5 : 1);
    showCursor?.(interactiveTarget ? 1 : 0);

    const nextMagneticTarget = target?.closest('[data-magnetic]');

    if (nextMagneticTarget !== magneticTarget) {
        resetMagneticTarget();

        if (nextMagneticTarget instanceof HTMLElement) {
            magneticTarget = nextMagneticTarget;
            magneticTargets.add(magneticTarget);
            magneticX = gsap.quickTo(magneticTarget, 'x', { duration: 0.35, ease: 'power3.out' });
            magneticY = gsap.quickTo(magneticTarget, 'y', { duration: 0.35, ease: 'power3.out' });
        }
    }

    if (magneticTarget instanceof HTMLElement) {
        const bounds = magneticTarget.getBoundingClientRect();
        magneticX?.((event.clientX - (bounds.left + bounds.width / 2)) * 0.14);
        magneticY?.((event.clientY - (bounds.top + bounds.height / 2)) * 0.14);
    }
};

const handlePointerOut = (event) => {
    if (event.relatedTarget === null) {
        showCursor?.(0);
        resetMagneticTarget();
    }
};

onMounted(() => {
    const supportsFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!supportsFinePointer || prefersReducedMotion || !cursor.value) {
        return;
    }

    isEnabled = true;
    document.documentElement.classList.add('has-custom-cursor');
    moveX = gsap.quickTo(cursor.value, 'x', { duration: 0.16, ease: 'power3.out' });
    moveY = gsap.quickTo(cursor.value, 'y', { duration: 0.16, ease: 'power3.out' });
    scaleCursor = gsap.quickTo(cursor.value, 'scale', { duration: 0.25, ease: 'power3.out' });
    showCursor = gsap.quickTo(cursor.value, 'autoAlpha', { duration: 0.16, ease: 'power3.out' });
    window.addEventListener('pointermove', handlePointerMove, { passive: true });
    window.addEventListener('pointerout', handlePointerOut);
});

onUnmounted(() => {
    if (!isEnabled) {
        return;
    }

    window.removeEventListener('pointermove', handlePointerMove);
    window.removeEventListener('pointerout', handlePointerOut);
    document.documentElement.classList.remove('has-custom-cursor');
    resetMagneticTarget();
    for (const target of magneticTargets) {
        gsap.killTweensOf(target);
        gsap.set(target, { clearProps: 'transform' });
    }
    magneticTargets.clear();
    gsap.killTweensOf(cursor.value);
});
</script>

<template>
    <span
        ref="cursor"
        aria-hidden="true"
        class="pointer-events-none fixed left-0 top-0 z-[90] size-6 rounded-full border border-[#c4a982] opacity-0 will-change-transform"
    >
        <span class="absolute left-1/2 top-1/2 size-1 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#c4a982]"></span>
    </span>
</template>
