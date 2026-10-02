<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionEyebrow from '../../Components/SectionEyebrow.vue';

defineProps({
    site: { type: Object, default: () => ({}) },
    about: { type: Object, default: () => ({}) },
});
</script>

<template>
    <Head title="Tentang Kami"><meta name="description" :content="about.description || 'DATA BELUM TERSEDIA'"></Head>
    <PublicLayout :site="site">
        <section class="bg-[#ede8de] px-6 pb-16 pt-36 sm:px-10 sm:pb-24 sm:pt-44 lg:px-14">
            <div class="mx-auto max-w-[1400px]">
                <SectionEyebrow number="01" label="Identitas yang kita bagi" />
                <h1 class="mt-8 max-w-6xl font-editorial text-6xl leading-[0.94] sm:text-8xl lg:text-[10rem]">SIAPA<br class="sm:hidden"> KITA?</h1>
                <div class="mt-12 grid gap-7 border-t border-black/15 pt-5 sm:grid-cols-3">
                    <p class="text-[9px] uppercase tracking-[0.15em] text-[#6b6b65]">Angkatan<br><span class="mt-2 block font-editorial text-2xl normal-case tracking-normal text-[#242424]">{{ site.cohort_name || 'DATA BELUM TERSEDIA' }}</span></p>
                    <p class="text-[9px] uppercase tracking-[0.15em] text-[#6b6b65]">Sekolah<br><span class="mt-2 block font-editorial text-2xl normal-case tracking-normal text-[#242424]">{{ site.school_name || 'DATA BELUM TERSEDIA' }}</span></p>
                    <p class="text-[9px] uppercase tracking-[0.15em] text-[#6b6b65]">Tahun<br><span class="mt-2 block font-editorial text-2xl normal-case tracking-normal text-[#242424]">{{ site.cohort_year || 'DATA BELUM TERSEDIA' }}</span></p>
                </div>
            </div>
        </section>
        <section class="px-6 py-20 sm:px-10 sm:py-28 lg:px-14">
            <div class="mx-auto grid max-w-[1400px] gap-10 lg:grid-cols-[0.7fr_1.3fr] lg:gap-20">
                <div><SectionEyebrow number="02" label="Cerita angkatan" /><p class="mt-5 font-editorial text-3xl">{{ about.major || 'DATA BELUM TERSEDIA' }}</p></div>
                <div>
                    <p class="font-editorial text-3xl leading-[1.25] sm:text-5xl">{{ about.description || 'DATA BELUM TERSEDIA' }}</p>
                    <div v-if="about.main_image_url" class="mt-12 aspect-[16/9] overflow-hidden bg-[#e4ded3]"><img :src="about.main_image_url" alt="Foto cerita angkatan" loading="lazy" class="h-full w-full object-cover"></div>
                    <div v-else class="archive-grain mt-12 grid aspect-[16/7] place-items-center border border-black/10 bg-[#ede8de] text-[9px] uppercase tracking-[0.16em] text-[#6b6b65]">Foto utama belum tersedia</div>
                    <p class="mt-10 max-w-3xl whitespace-pre-line text-sm leading-8 text-[#6b6b65]">{{ about.story || 'DATA BELUM TERSEDIA' }}</p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>