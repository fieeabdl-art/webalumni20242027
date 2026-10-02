<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, Menu, X } from '@lucide/vue';

defineProps({
    site: { type: Object, default: () => ({}) },
});

const page = usePage();
const scrolled = ref(false);
const menuOpen = ref(false);
const links = [
    { label: 'Beranda', href: '/' },
    { label: 'Tentang Kami', href: '/tentang-kami' },
    { label: 'Inilah Kami', href: '/anggota' },
    { label: 'Guru Kami', href: '/guru' },
    { label: 'Kenangan Kami', href: '/kenangan' },
];

const updateScroll = () => {
    scrolled.value = window.scrollY > 32;
};

const closeMenu = () => {
    menuOpen.value = false;
};

onMounted(() => window.addEventListener('scroll', updateScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', updateScroll));
</script>

<template>
    <div class="min-h-screen">
        <header
            class="fixed inset-x-0 top-0 z-40 transition-colors duration-500"
            :class="scrolled || menuOpen ? 'border-b border-black/5 bg-[#f5f2ec]/95 text-[#242424] backdrop-blur-sm' : 'bg-transparent text-white'"
        >
            <nav class="mx-auto flex h-[76px] max-w-[1600px] items-center justify-between px-5 sm:px-8 lg:px-14" aria-label="Navigasi utama">
                <Link href="/" class="focus-ring flex items-center gap-3" @click="closeMenu">
                    <span class="grid size-9 place-items-center border border-current/45 font-editorial text-lg">A</span>
                    <span class="max-w-32 text-[10px] font-semibold uppercase leading-[1.35] tracking-[0.16em] sm:max-w-48">
                        {{ site.cohort_name || 'DATA BELUM TERSEDIA' }}
                    </span>
                </Link>

                <div class="hidden items-center gap-7 lg:flex">
                    <Link
                        v-for="item in links"
                        :key="item.href"
                        :href="item.href"
                        class="focus-ring py-2 text-[9px] font-semibold uppercase tracking-[0.16em] transition-opacity hover:opacity-60"
                        :aria-current="page.url === item.href ? 'page' : undefined"
                    >{{ item.label }}</Link>
                </div>

                <button
                    type="button"
                    class="focus-ring grid size-11 place-items-center lg:hidden"
                    :aria-label="menuOpen ? 'Tutup navigasi' : 'Buka navigasi'"
                    :aria-expanded="menuOpen"
                    @click="menuOpen = !menuOpen"
                >
                    <X v-if="menuOpen" :size="19" />
                    <Menu v-else :size="20" />
                </button>
            </nav>
        </header>

        <Transition name="menu">
            <div v-if="menuOpen" class="fixed inset-0 z-30 flex flex-col justify-center bg-[#f5f2ec] px-8 pt-20 lg:hidden">
                <div class="mb-8 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#a38b68]">Jelajahi arsip</div>
                <Link
                    v-for="(item, index) in links"
                    :key="item.href"
                    :href="item.href"
                    class="focus-ring flex items-center justify-between border-b border-black/10 py-4 font-editorial text-3xl"
                    @click="closeMenu"
                >
                    <span>{{ item.label }}</span><span class="text-xs text-[#a38b68]">0{{ index + 1 }}</span>
                </Link>
            </div>
        </Transition>

        <main><slot /></main>

        <footer class="bg-[#111] px-6 py-10 text-[#f5f2ec] sm:px-10 lg:px-14">
            <div class="mx-auto flex max-w-[1600px] flex-col gap-7 border-t border-white/15 pt-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-editorial text-2xl">{{ site.cohort_name || 'DATA BELUM TERSEDIA' }}</p>
                    <p class="mt-2 text-[9px] uppercase tracking-[0.18em] text-white/45">{{ site.school_name || 'DATA BELUM TERSEDIA' }} · {{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</p>
                </div>
                <a v-if="site.instagram" :href="site.instagram" target="_blank" rel="noreferrer" class="focus-ring inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.15em]">
                    Instagram <ArrowUpRight :size="14" />
                </a>
                <p class="text-[9px] uppercase tracking-[0.12em] text-white/45">{{ site.footer_text || 'DATA BELUM TERSEDIA' }}</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.menu-enter-active,
.menu-leave-active { transition: opacity 260ms ease, transform 260ms ease; }
.menu-enter-from,
.menu-leave-to { opacity: 0; transform: translateY(-8px); }
</style>