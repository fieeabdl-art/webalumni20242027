<template>
    <div class="section-fade pointer-events-none absolute inset-0 z-0" aria-hidden="true">
        <span class="section-fade__edge section-fade__edge--top"></span>
        <span class="section-fade__edge section-fade__edge--bottom"></span>
    </div>
</template>

<style scoped>
.section-fade__edge {
    position: absolute;
    right: 0;
    left: 0;
    height: clamp(5rem, 12vw, 11rem);
}

.section-fade__edge--top {
    top: 0;
    background: linear-gradient(to bottom, #151515, rgb(21 21 21 / 0%));
}

.section-fade__edge--bottom {
    bottom: 0;
    background: linear-gradient(to top, #151515, rgb(21 21 21 / 0%));
}
</style>
