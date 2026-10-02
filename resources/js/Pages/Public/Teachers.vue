<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';
import TeacherCard from '../../Components/TeacherCard.vue';

defineProps({
    site: { type: Object, default: () => ({}) },
    teachers: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Guru Kami"><meta name="description" content="Catatan untuk para guru yang membersamai perjalanan angkatan."></Head>
    <PublicLayout :site="site">
        <section class="px-6 pb-16 pt-36 sm:px-10 sm:pb-24 sm:pt-44 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="03" label="Mereka yang membersamai" />
                <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_0.6fr] lg:items-end">
                    <h1 class="font-editorial text-6xl leading-[0.94] sm:text-8xl">GURU<br> KAMI.</h1>
                    <p class="max-w-md pb-2 text-sm leading-7 text-[#6b6b65]">Setiap pelajaran meninggalkan lebih dari sekadar catatan. Halaman ini menyimpan ruang untuk menyampaikan terima kasih.</p>
                </div>
                <div v-if="teachers.length" class="mt-16 grid gap-x-10 lg:grid-cols-2">
                    <TeacherCard v-for="teacher in teachers" :key="teacher.id" :teacher="teacher" />
                </div>
                <p v-else class="mt-12 border-y border-black/15 py-8 text-[10px] uppercase tracking-[0.16em] text-[#6b6b65]">DATA BELUM TERSEDIA</p>
            </div>
        </section>
    </PublicLayout>
</template>