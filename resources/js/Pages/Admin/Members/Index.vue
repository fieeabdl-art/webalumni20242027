<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({ members: { type: Array, default: () => [] } });
const search = ref('');
const filteredMembers = computed(() => {
    const query = search.value.trim().toLocaleLowerCase('id');

    if (!query) {
        return props.members;
    }

    return props.members.filter((member) => [member.name, member.nickname, member.major]
        .some((value) => value?.toLocaleLowerCase('id').includes(query)));
});

const remove = (member) => {
    if (window.confirm(`Hapus data ${member.name}?`)) router.delete(`/admin/anggota/${member.id}`);
};
</script>

<template>
    <AdminLayout title="Anggota">
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-black/10 pb-6"><div><p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#73552f]">Data personal</p><h1 class="mt-2 font-editorial text-4xl leading-tight sm:text-5xl">Anggota</h1><p class="mt-2 text-sm text-[#55514b]">{{ members.length }} profil tersimpan</p></div><Link href="/admin/anggota/create" class="admin-button-primary focus-ring"><Plus :size="16" /> Tambah anggota</Link></header>
        <section class="admin-panel mt-5" aria-label="Pencarian anggota"><label class="admin-label" for="member-search">Cari anggota</label><input id="member-search" v-model="search" class="admin-input max-w-xl" type="search" placeholder="Nama atau jurusan" autocomplete="off"><p class="mt-2 text-xs text-[#55514b]" aria-live="polite">{{ filteredMembers.length }} hasil</p></section>
        <div class="mt-5 hidden overflow-hidden border border-black/10 bg-white/35 xl:block">
            <table class="admin-table">
                <thead><tr><th>Nama</th><th>Jurusan</th><th>Status</th><th>Cutout</th><th>Urutan</th><th class="text-right">Aksi</th></tr></thead>
                <tbody><tr v-for="member in filteredMembers" :key="member.id"><td><span class="font-medium">{{ member.name }}</span><span v-if="member.nickname" class="mt-1 block text-xs text-[#55514b]">{{ member.nickname }}</span></td><td class="text-[#55514b]">{{ member.major?.toLocaleUpperCase('id') || 'DATA BELUM TERSEDIA' }}</td><td><span class="inline-flex items-center gap-2 text-xs" :class="member.status ? 'text-[#344b36]' : 'text-[#713a35]'"><span class="size-1.5 rounded-full" :class="member.status ? 'bg-[#526b54]' : 'bg-[#8b4b45]'"></span>{{ member.status ? 'Tayang' : 'Draft' }}</span></td><td><span class="text-xs font-medium" :class="member.poster_uses_cutout ? 'text-[#344b36]' : 'text-[#713a35]'">Cutout: {{ member.poster_uses_cutout ? 'ada' : 'belum' }}</span></td><td class="text-[#55514b]">{{ member.sort_order }}</td><td><div class="flex justify-end gap-1"><Link :href="`/admin/anggota/${member.id}/edit`" class="focus-ring grid size-11 place-items-center text-[#55514b] hover:bg-[#eee9e0] hover:text-[#171717]" :aria-label="`Edit ${member.name}`"><Pencil :size="16" /></Link><button type="button" class="focus-ring grid size-11 place-items-center text-[#8b4b45] hover:bg-[#8b4b45]/5" :aria-label="`Hapus ${member.name}`" @click="remove(member)"><Trash2 :size="16" /></button></div></td></tr></tbody>
            </table>
            <p v-if="!filteredMembers.length" class="px-5 py-10 text-center text-sm text-[#55554f]">{{ members.length ? 'Tidak ada anggota yang cocok dengan pencarian.' : 'DATA BELUM TERSEDIA' }}</p>
        </div>
        <div class="mt-4 grid gap-3 xl:hidden">
            <article v-for="member in filteredMembers" :key="member.id" class="admin-panel flex min-w-0 items-start justify-between gap-3">
            <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ member.name }}</p><p v-if="member.nickname" class="mt-1 truncate text-xs text-[#55514b]">{{ member.nickname }}</p><p class="mt-3 break-words text-xs text-[#55514b]">{{ member.major?.toLocaleUpperCase('id') || 'DATA BELUM TERSEDIA' }}</p><p class="mt-2 inline-flex items-center gap-2 text-xs" :class="member.status ? 'text-[#344b36]' : 'text-[#713a35]'"><span class="size-1.5 rounded-full" :class="member.status ? 'bg-[#526b54]' : 'bg-[#8b4b45]'"></span>{{ member.status ? 'Tayang' : 'Draft' }} <span class="text-[#55514b]">· Cutout: {{ member.poster_uses_cutout ? 'ada' : 'belum' }} · Urutan {{ member.sort_order }}</span></p></div>
                <div class="flex shrink-0"><Link :href="`/admin/anggota/${member.id}/edit`" class="focus-ring grid size-11 place-items-center text-[#55554f]" :aria-label="`Edit ${member.name}`"><Pencil :size="16" /></Link><button type="button" class="focus-ring grid size-11 place-items-center text-[#8b4b45]" :aria-label="`Hapus ${member.name}`" @click="remove(member)"><Trash2 :size="16" /></button></div>
            </article>
            <p v-if="!filteredMembers.length" class="admin-panel py-8 text-center text-sm text-[#55554f]">{{ members.length ? 'Tidak ada anggota yang cocok dengan pencarian.' : 'DATA BELUM TERSEDIA' }}</p>
        </div>
    </AdminLayout>
</template>