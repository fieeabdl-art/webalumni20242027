<script setup>
import { computed } from 'vue';

const props = defineProps({
    points: { type: Array, default: () => [] },
    connections: { type: Array, default: () => [] },
    width: { type: Number, default: 0 },
    height: { type: Number, default: 0 },
    reducedMotion: { type: Boolean, default: false },
    isMobile: { type: Boolean, default: false },
    activeId: { type: String, default: '' },
});

const paths = computed(() => {
    const pointsById = new Map(props.points.map((point) => [point.id, point]));

    return props.connections.flatMap(([fromId, toId], index) => {
        const from = pointsById.get(fromId);
        const to = pointsById.get(toId);

        if (!from || !to) {
            return [];
        }

        const distance = Math.hypot(to.x - from.x, to.y - from.y);
        const fullSag = Math.min(90, Math.max(16, distance * 0.12));
        const active = props.activeId === fromId || props.activeId === toId;
        const sag = active ? fullSag * 0.45 : fullSag;
        const id = `${fromId}-${toId}`;

        return [{
            id,
            fromId,
            toId,
            index,
            sag,
            path: `M ${from.x} ${from.y} Q ${(from.x + to.x) / 2} ${(from.y + to.y) / 2 + sag} ${to.x} ${to.y}`,
            active,
        }];
    });
});
</script>

<template>
    <svg class="red-thread" :class="{ 'red-thread--static': reducedMotion, 'red-thread--mobile': isMobile }" :viewBox="`0 0 ${width} ${height}`" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <defs>
            <filter id="red-thread-shadow" x="-20%" y="-20%" width="140%" height="150%">
                <feGaussianBlur in="SourceAlpha" stdDeviation="1.5" />
                <feOffset dy="2.5" />
                <feComponentTransfer><feFuncA type="linear" slope=".35" /></feComponentTransfer>
                <feMerge><feMergeNode /><feMergeNode in="SourceGraphic" /></feMerge>
            </filter>
        </defs>
        <g v-for="thread in paths" :key="thread.id" class="red-thread__group" :class="{
            'red-thread__group--active': thread.active,
            'red-thread__group--dimmed': activeId && !thread.active,
        }" :data-thread-index="thread.index">
            <path class="red-thread__shadow" :d="thread.path" />
            <path class="red-thread__line" :d="thread.path" filter="url(#red-thread-shadow)" />
            <path class="red-thread__fiber" :d="thread.path" />
        </g>
        <circle v-for="point in points" :key="`knot-${point.id}`" class="red-thread__knot" :cx="point.x" :cy="point.y" r="2.1" />
    </svg>
</template>

<style>
.red-thread {
    position: absolute;
    z-index: 3;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: visible;
    pointer-events: none;
}

.red-thread__group {
    opacity: 0.9;
    transform-box: fill-box;
    transform-origin: center;
    transition: opacity 240ms ease;
}

.red-thread__group--dimmed {
    opacity: 0.5;
}

.red-thread__group--active {
    opacity: 1;
}

.red-thread__group--active .red-thread__line {
    stroke-width: 2.5;
}

.red-thread__group--active .red-thread__fiber {
    opacity: 0.58;
}

.red-thread__shadow,
.red-thread__line,
.red-thread__fiber {
    fill: none;
    stroke-linecap: round;
    vector-effect: non-scaling-stroke;
}

.red-thread__shadow {
    stroke: #000;
    stroke-width: 3.5;
    opacity: 0.35;
    filter: blur(1.5px);
}

.red-thread__line {
    stroke: #a8433b;
    stroke-width: 2;
}

.red-thread__fiber {
    stroke: #e1796d;
    stroke-width: 0.65;
    stroke-dasharray: 1.2 4;
    opacity: 0.34;
}

.red-thread__knot {
    fill: #a8433b;
    stroke: #f1a094;
    stroke-width: 0.8;
}

@media (max-width: 1023px) {
    .red-thread__line {
        stroke-width: 1.6;
    }

    .red-thread__group:nth-of-type(n + 7) {
        display: none;
    }

    .red-thread__fiber {
        display: none;
    }
}

@media (max-width: 639px) {
    .red-thread__group:nth-of-type(n + 4),
    .red-thread__knot {
        display: none;
    }

    .red-thread__line {
        stroke-width: 1.5;
    }
}

@media (prefers-reduced-motion: reduce) {
    .red-thread__group {
        transition: none;
    }
}
</style>
