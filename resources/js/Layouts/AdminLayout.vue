<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Camera, CircleHelp, House, Image, LayoutDashboard, LogOut, Menu, Settings, UserRound, Users, X } from '@lucide/vue';

defineProps({ title: { type: String, required: true } });

const page = usePage();
const menuOpen = ref(false);
const isDesktop = ref(false);
const sidebar = ref(null);
const menuTrigger = ref(null);
const validationErrors = computed(() => Object.values(page.props.errors || {})
    .flat()
    .filter((message) => typeof message === 'string'));
const errorMessage = computed(() => page.props.flash?.error || validationErrors.value[0] || '');
const navigation = [
    { label: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { label: 'Beranda', href: '/admin/beranda', icon: House },
    { label: 'Tentang Kami', href: '/admin/tentang-kami', icon: BookOpen },
    { label: 'Anggota', href: '/admin/anggota', icon: Users },
    { label: 'Guru', href: '/admin/guru', icon: UserRound },
    { label: 'Kenangan', href: '/admin/kenangan', icon: Image },
    { label: 'Kata-Kata', href: '/admin/kata-kata', icon: CircleHelp },
    { label: 'Pengaturan', href: '/admin/pengaturan', icon: Settings },
];

const closeMenu = () => {
    menuOpen.value = false;
};

const updateViewport = () => {
    isDesktop.value = window.matchMedia('(min-width: 1024px)').matches;

    if (isDesktop.value) {
        closeMenu();
    }
};

const handleKeydown = (event) => {
    if (event.key === 'Escape') {
        closeMenu();

        return;
    }

    if (event.key === 'Tab' && menuOpen.value) {
        const focusableElements = sidebar.value?.querySelectorAll('a[href], button:not([disabled])');
        const firstElement = focusableElements?.[0];
        const lastElement = focusableElements?.[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement?.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement?.focus();
        }
    }
};

watch(menuOpen, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';

    if (isOpen) {
        nextTick(() => sidebar.value?.querySelector('button[aria-label="Tutup menu"]')?.focus());
    } else if (menuTrigger.value && !isDesktop.value) {
        nextTick(() => menuTrigger.value?.focus());
    }
});

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', updateViewport, { passive: true });
    updateViewport();
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('resize', updateViewport);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen bg-[#f5f2ec] text-[#171717] lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
        <aside id="admin-sidebar" ref="sidebar" :inert="!menuOpen && !isDesktop" class="fixed inset-y-0 left-0 z-40 flex h-dvh w-[min(84vw,300px)] flex-col border-r border-[#d8d1c6] bg-[#eee9e0] px-5 py-5 transition-transform motion-reduce:transition-none lg:sticky lg:top-0 lg:w-auto lg:translate-x-0" :class="menuOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex shrink-0 items-center justify-between border-b border-[#d8d1c6] pb-5">
                <Link href="/admin" class="focus-ring flex items-center gap-3" @click="closeMenu">
                    <span class="grid size-10 place-items-center border border-[#987344] font-editorial text-xl">A</span>
                    <span><span class="block text-[10px] font-semibold uppercase tracking-[0.15em]">Arsip Angkatan</span><span class="mt-1 block text-[10px] uppercase tracking-[0.1em] text-[#55514b]">Ruang pengurus</span></span>
                </Link>
                <button type="button" class="focus-ring grid size-11 place-items-center lg:hidden" aria-label="Tutup menu" @click="closeMenu"><X :size="18" /></button>
            </div>
            <nav class="mt-6 min-h-0 flex-1 space-y-1 overflow-y-auto" aria-label="Navigasi CMS">
                <Link v-for="item in navigation" :key="item.href" :href="item.href" class="focus-ring flex min-h-11 items-center gap-3 px-3 text-xs transition-colors motion-reduce:transition-none" :class="page.url === item.href || (item.href !== '/admin' && page.url.startsWith(`${item.href}/`)) ? 'border-l-2 border-[#987344] bg-[#151515] pl-[10px] font-medium text-[#f5f2ec]' : 'border-l-2 border-transparent text-[#55514b] hover:bg-black/5 hover:text-[#171717]'" :aria-current="page.url === item.href || (item.href !== '/admin' && page.url.startsWith(`${item.href}/`)) ? 'page' : undefined" @click="closeMenu">
                    <component :is="item.icon" :size="15" :stroke-width="1.7" />{{ item.label }}
                </Link>
            </nav>
            <div class="mt-5 shrink-0 border-t border-[#d8d1c6] pt-4">
                <Link href="/" class="focus-ring mb-1 flex min-h-11 items-center gap-3 px-3 text-xs text-[#55514b] hover:text-[#171717]" @click="closeMenu"><Camera :size="15" /> Lihat website</Link>
                <Link href="/admin/logout" method="post" as="button" class="focus-ring flex min-h-11 w-full items-center gap-3 px-3 text-left text-xs text-[#8b4b45] hover:bg-[#8b4b45]/5" @click="closeMenu"><LogOut :size="15" /> Keluar</Link>
            </div>
        </aside>
        <button v-if="menuOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" aria-label="Tutup navigasi" @click="closeMenu"></button>
        <div class="min-w-0" :inert="menuOpen">
            <header class="sticky top-0 z-20 flex min-h-[68px] items-center justify-between gap-3 border-b border-[#d8d1c6] bg-[#f5f2ec]/95 px-4 backdrop-blur sm:px-7">
                <div class="flex min-w-0 items-center gap-3"><button ref="menuTrigger" type="button" class="focus-ring grid size-11 shrink-0 place-items-center lg:hidden" aria-label="Buka navigasi" aria-controls="admin-sidebar" :aria-expanded="menuOpen" @click="menuOpen = true"><Menu :size="19" /></button><p class="truncate text-xs font-semibold uppercase tracking-[0.1em]">{{ title }}</p></div>
                <div class="min-w-0 text-right"><span class="block truncate text-[10px] font-semibold uppercase tracking-[0.08em] text-[#171717]">{{ page.props.auth?.user?.name || 'PENGURUS' }}</span><span class="mt-1 hidden truncate text-[10px] text-[#55514b] sm:block">{{ page.props.auth?.user?.email }}</span></div>
            </header>
            <main class="mx-auto min-h-[calc(100dvh-68px)] max-w-[1440px] px-4 py-7 sm:px-7 sm:py-9 xl:px-10">
                <div v-if="page.props.flash?.success" role="status" aria-live="polite" class="mb-6 border-l-2 border-[#526b54] bg-[#526b54]/[0.07] px-4 py-3 text-sm text-[#344b36]">{{ page.props.flash.success }}</div>
                <div v-if="errorMessage" role="alert" aria-live="assertive" class="mb-6 border-l-2 border-[#8b4b45] bg-[#8b4b45]/[0.06] px-4 py-3 text-sm text-[#713a35]">
                    <p class="font-semibold">Periksa kembali data yang dikirim.</p>
                    <ul v-if="validationErrors.length" class="mt-2 list-inside list-disc space-y-1">
                        <li v-for="(message, index) in validationErrors" :key="`${index}-${message}`">{{ message }}</li>
                    </ul>
                    <p v-else class="mt-1">{{ errorMessage }}</p>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>