<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { ArrowLeft, ArrowRight, ArrowUpRight, X } from '@lucide/vue';

const props = defineProps({
    members: { type: Array, default: () => [] },
    index: { type: Number, default: -1 },
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'navigate']);
const dialog = ref(null);
const activeMember = computed(() => props.members[props.index] ?? null);

watch(() => props.open, async (isOpen) => {
    await nextTick();

    if (!dialog.value) {
        return;
    }

    if (isOpen && !dialog.value.open) {
        dialog.value.showModal();
        dialog.value.querySelector('[data-dialog-close]')?.focus();
    } else if (!isOpen && dialog.value.open) {
        dialog.value.close();
    }
}, { flush: 'post' });

function handleKeydown(event) {
    if (event.key === 'ArrowLeft') {
        event.preventDefault();
        emit('navigate', -1);
    } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        emit('navigate', 1);
    } else if (event.key === 'Escape') {
        event.preventDefault();
        closeDialog();
    }
}

function closeDialog() {
    emit('close');
}

onBeforeUnmount(() => {
    if (dialog.value?.open) {
        dialog.value.close();
    }
});
</script>

<template>
    <dialog
        ref="dialog"
        class="member-lightbox m-auto max-h-[92svh] w-[min(94vw,1100px)] max-w-none overflow-y-auto border border-white/15 bg-[#151515] p-0 text-[#f5f2ec] shadow-2xl"
        aria-labelledby="member-lightbox-title"
        @cancel.prevent="closeDialog"
        @keydown="handleKeydown"
        @click.self="closeDialog"
    >
        <div v-if="activeMember" class="relative grid min-h-[min(82svh,720px)] lg:grid-cols-[1fr_0.9fr]">
            <div class="relative min-h-[52svh] bg-[#211d19] lg:min-h-[680px]">
                <img
                    v-if="activeMember.photo_url"
                    :src="activeMember.photo_url"
                    :alt="activeMember.name || 'DATA BELUM TERSEDIA'"
                    class="absolute inset-0 h-full w-full object-contain"
                    fetchpriority="high"
                >
                <div v-else class="archive-grain absolute inset-0 grid place-items-center text-center text-xs font-semibold uppercase tracking-[0.16em] text-[#d7d1c7]">
                    DATA BELUM TERSEDIA
                </div>
            </div>

            <div class="flex flex-col justify-between gap-10 p-6 sm:p-10 lg:p-12">
                <div>
                    <div class="flex items-start justify-between gap-5">
                        <p class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#d7d1c7]">Catatan personal</p>
                        <button
                            data-dialog-close
                            type="button"
                            class="focus-ring -mr-2 -mt-2 grid size-11 shrink-0 place-items-center text-white/75 transition-colors hover:text-white"
                            aria-label="Tutup detail anggota"
                            @click="closeDialog"
                        >
                            <X :size="20" />
                        </button>
                    </div>
                    <p class="mt-8 text-[9px] font-semibold uppercase tracking-[0.17em] text-[#d06a5f]">
                        {{ activeMember.nickname || 'DATA BELUM TERSEDIA' }}
                    </p>
                    <h2 id="member-lightbox-title" class="mt-3 font-editorial text-4xl leading-tight sm:text-5xl">{{ activeMember.name || 'DATA BELUM TERSEDIA' }}</h2>
                    <p class="mt-5 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#d7d1c7]">
                        {{ [activeMember.class_name, activeMember.major].filter(Boolean).join(' · ') || 'DATA BELUM TERSEDIA' }}
                    </p>
                    <blockquote class="mt-9 border-l border-[#a8433b] pl-5 font-editorial text-xl italic leading-8 text-[#e5ddd2]">
                        {{ activeMember.quote || 'DATA BELUM TERSEDIA' }}
                    </blockquote>
                    <p class="mt-7 whitespace-pre-line text-sm leading-7 text-white/70">
                        {{ activeMember.bio || 'DATA BELUM TERSEDIA' }}
                    </p>
                    <a
                        v-if="activeMember.instagram"
                        :href="activeMember.instagram"
                        target="_blank"
                        rel="noreferrer"
                        class="focus-ring mt-8 inline-flex min-h-11 items-center gap-2 text-[9px] font-semibold uppercase tracking-[0.17em] text-white"
                    >
                        Instagram <ArrowUpRight :size="14" />
                    </a>
                </div>

                <nav class="flex items-center justify-between border-t border-white/20 pt-4" aria-label="Navigasi anggota">
                    <button
                        type="button"
                        class="focus-ring inline-flex min-h-11 items-center gap-2 text-[9px] font-semibold uppercase tracking-[0.15em] text-white/80 hover:text-white"
                        aria-label="Anggota sebelumnya"
                        :disabled="members.length < 2"
                        @click="emit('navigate', -1)"
                    >
                        <ArrowLeft :size="16" /> Sebelumnya
                    </button>
                    <span class="text-[9px] uppercase tracking-[0.16em] text-white/50">{{ index + 1 }} / {{ members.length }}</span>
                    <button
                        type="button"
                        class="focus-ring inline-flex min-h-11 items-center gap-2 text-[9px] font-semibold uppercase tracking-[0.15em] text-white/80 hover:text-white"
                        aria-label="Anggota berikutnya"
                        :disabled="members.length < 2"
                        @click="emit('navigate', 1)"
                    >
                        Berikutnya <ArrowRight :size="16" />
                    </button>
                </nav>
            </div>
        </div>
    </dialog>
</template>

<style scoped>
.member-lightbox::backdrop {
    background: rgb(8 8 8 / 86%);
    backdrop-filter: blur(8px);
}

.member-lightbox[open] {
    animation: lightbox-in 180ms ease-out both;
}

@keyframes lightbox-in {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .member-lightbox[open] {
        animation: none;
    }
}
</style>
