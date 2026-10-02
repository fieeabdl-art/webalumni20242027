<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';
import SearchFilter from '../../Components/SearchFilter.vue';
import MemberCard from '../../Components/MemberCard.vue';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    members: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
    majors: { type: Array, default: () => [] },
});

const search = ref('');
const selectedClass = ref('');
const selectedMajor = ref('');

const filteredMembers = computed(() => props.members.filter((member) => {
    const term = search.value.trim().toLowerCase();
    const matchesSearch = !term || [member.name, member.nickname, member.class_name, member.major].some((value) => value?.toLowerCase().includes(term));
    return matchesSearch
        && (!selectedClass.value || member.class_name === selectedClass.value)
        && (!selectedMajor.value || member.major === selectedMajor.value);
}));
</script>

<template>
    <Head title="Inilah Kami"><meta name="description" content="Kenali anggota dalam arsip angkatan."></Head>
    <PublicLayout :site="site">
        <section class="px-6 pb-12 pt-36 sm:px-10 sm:pb-16 sm:pt-44 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="02" label="Wajah di dalam cerita" />
                <div class="mt-7 flex flex-wrap items-end justify-between gap-5">
                    <h1 class="max-w-4xl font-editorial text-6xl leading-[0.95] sm:text-8xl">INILAH<br class="sm:hidden"> KAMI.</h1>
                    <p class="pb-2 text-[9px] uppercase tracking-[0.14em] text-[#6b6b65]">{{ members.length }} nama tersimpan</p>
                </div>
                <div class="mt-12"><SearchFilter v-model="search" v-model:class-value="selectedClass" v-model:major-value="selectedMajor" :classes="classes" :majors="majors" /></div>
                <div v-if="filteredMembers.length" class="mt-10 grid gap-x-5 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                    <MemberCard v-for="member in filteredMembers" :key="member.id" :member="member" />
                </div>
                <p v-else class="border-b border-black/15 py-8 text-[10px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</p>
            </div>
        </section>
    </PublicLayout>
</template>