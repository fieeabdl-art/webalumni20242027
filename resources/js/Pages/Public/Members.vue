<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';
import SearchFilter from '../../Components/SearchFilter.vue';
import MemberPoster from '../../Components/MemberPoster.vue';
import MemberDetailLightbox from '../../Components/MemberDetailLightbox.vue';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    members: { type: Array, default: () => [] },
    majors: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
});

const search = ref('');
const selectedMajor = ref('');
const selectedClass = ref('');
const selectedIndex = ref(-1);
const isLightboxOpen = computed(() => selectedIndex.value >= 0);

const filteredMembers = computed(() => props.members.filter((member) => {
    const term = search.value.trim().toLowerCase();
    const matchesSearch = !term || [member.name, member.nickname, member.major, member.class_name].some((value) => value?.toLowerCase().includes(term));
    return matchesSearch
        && (!selectedMajor.value || member.major === selectedMajor.value)
        && (!selectedClass.value || member.class_name === selectedClass.value);
}));

function openMember(member) {
    selectedIndex.value = props.members.findIndex((item) => item.id === member.id);
}

function navigateMember(direction) {
    if (props.members.length < 2) {
        return;
    }

    selectedIndex.value = (selectedIndex.value + direction + props.members.length) % props.members.length;
}

function closeLightbox() {
    selectedIndex.value = -1;
}
</script>

<template>
    <Head title="Inilah Kami"><meta name="description" content="Kenali anggota dalam arsip angkatan."></Head>
    <PublicLayout :site="site">
        <section class="px-6 pb-12 pt-36 sm:px-10 sm:pb-16 sm:pt-44 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="02" label="Wajah di dalam cerita" />
                <div class="mt-7 flex flex-wrap items-end justify-between gap-5">
                    <h1 class="max-w-4xl font-editorial text-6xl leading-[0.95] sm:text-8xl">INILAH<br class="sm:hidden"> KAMI.</h1>
                    <p class="pb-2 text-[11px] font-medium uppercase tracking-[0.12em] text-[#55514b]">{{ members.length }} anggota tersimpan</p>
                </div>
                <div class="mt-10">
                    <SearchFilter
                        v-model="search"
                        v-model:major-value="selectedMajor"
                        v-model:class-value="selectedClass"
                        :majors="majors"
                        :classes="classes"
                    />
                </div>
                <TransitionGroup
                    v-if="filteredMembers.length"
                    tag="div"
                    name="poster-grid"
                    class="relative mx-auto mt-10 grid max-w-[1240px] grid-cols-1 justify-items-center gap-3"
                    :class="filteredMembers.length === 1 ? 'md:grid-cols-1' : 'md:grid-cols-2 xl:grid-cols-3'"
                >
                    <MemberPoster
                        v-for="member in filteredMembers"
                        :key="member.id"
                        :member="member"
                        :site="site"
                        :index="members.indexOf(member)"
                        :class="filteredMembers.length === 1 ? 'w-full max-w-[420px]' : ''"
                        @open="openMember"
                    />
                </TransitionGroup>
                <p v-if="filteredMembers.length" class="sr-only" aria-live="polite" aria-atomic="true">
                    {{ filteredMembers.length }} anggota ditemukan
                </p>
                <div
                    v-else
                    class="mt-8 border-y border-[#d8d1c6] py-10 sm:py-14"
                    role="status"
                    aria-live="polite"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#73552f]">Arsip anggota</p>
                    <p class="mt-3 font-editorial text-2xl sm:text-3xl">DATA BELUM TERSEDIA</p>
                    <p v-if="members.length" class="mt-2 text-sm leading-6 text-[#55514b]">Coba ubah kata pencarian atau filter kelas dan jurusan.</p>
                    <p v-else class="mt-2 text-sm leading-6 text-[#55514b]">Profil anggota yang telah dipublikasikan akan hadir di sini.</p>
                </div>
            </div>
        </section>
        <MemberDetailLightbox
            :members="members"
            :index="selectedIndex"
            :open="isLightboxOpen"
            @close="closeLightbox"
            @navigate="navigateMember"
        />
    </PublicLayout>
</template>

<style scoped>
.poster-grid-enter-active,
.poster-grid-leave-active,
.poster-grid-move {
    transition: opacity 240ms ease, transform 240ms ease;
}

.poster-grid-enter-from,
.poster-grid-leave-to {
    opacity: 0;
    transform: translateY(12px);
}

.poster-grid-leave-active {
    position: absolute;
}

@media (prefers-reduced-motion: reduce) {
    .poster-grid-enter-active,
    .poster-grid-leave-active,
    .poster-grid-move {
        transition-duration: 0.01ms !important;
    }
}
</style>