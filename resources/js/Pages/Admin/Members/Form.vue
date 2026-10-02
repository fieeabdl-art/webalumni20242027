<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import ImageUploadField from '../../../Components/Admin/ImageUploadField.vue';

const props = defineProps({
    member: { type: Object, default: null },
    majors: { type: Array, default: () => [] },
});
const selectedMajor = props.majors.find((major) => major.toLocaleUpperCase('id') === props.member?.major?.toLocaleUpperCase('id')) || '';
const form = useForm({
    name: props.member?.name || '', nickname: props.member?.nickname || '',
    class_name: props.member?.class_name || '', major: selectedMajor,
    photo: null, photo_cutout: null, quote: props.member?.quote || '', bio: props.member?.bio || '',
    instagram: props.member?.instagram || '', status: props.member?.status ?? false, sort_order: props.member?.sort_order ?? 0,
});
const submit = () => form.transform((data) => ({ ...data, status: data.status ? 1 : 0 })).post(
    props.member ? `/admin/anggota/${props.member.id}?_method=PUT` : '/admin/anggota',
    { forceFormData: true },
);
</script>

<template>
    <AdminLayout :title="member ? 'Edit Anggota' : 'Tambah Anggota'">
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-black/10 pb-6"><div><p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#73552f]">Data personal</p><h1 class="mt-2 font-editorial text-4xl leading-tight sm:text-5xl">{{ member ? 'Edit anggota' : 'Tambah anggota' }}</h1><p class="mt-2 text-sm text-[#55514b]">Lengkapi profil yang telah diverifikasi untuk arsip.</p></div><Link href="/admin/anggota" class="admin-button-secondary focus-ring">Kembali ke daftar</Link></header>
        <form class="mx-auto mt-6 grid w-full max-w-4xl gap-4 md:grid-cols-[minmax(0,1.1fr)_minmax(240px,0.9fr)]" @submit.prevent="submit">
            <section class="admin-panel grid content-start gap-5">
                <h2 class="admin-section-title">Informasi profil</h2>
                <div><label class="admin-label" for="name">Nama lengkap</label><input id="name" v-model="form.name" class="admin-input" required :aria-invalid="Boolean(form.errors.name)"><p v-if="form.errors.name" class="mt-1 text-xs text-[#713a35]">{{ form.errors.name }}</p></div>
                <div><label class="admin-label" for="nickname">Nama panggilan</label><input id="nickname" v-model="form.nickname" class="admin-input"></div>
                <div><label class="admin-label" for="class_name">Kelas</label><input id="class_name" v-model="form.class_name" class="admin-input" :aria-invalid="Boolean(form.errors.class_name)"><p v-if="form.errors.class_name" class="mt-1 text-xs text-[#713a35]">{{ form.errors.class_name }}</p></div>
                <div>
                    <label class="admin-label" for="major">Jurusan <span aria-hidden="true">*</span></label>
                    <select id="major" v-model="form.major" class="admin-input" required :aria-invalid="Boolean(form.errors.major)" :aria-describedby="form.errors.major ? 'major-error' : undefined">
                        <option value="" disabled>Pilih jurusan</option>
                        <option v-for="major in majors" :key="major" :value="major">{{ major }}</option>
                    </select>
                    <p v-if="form.errors.major" id="major-error" class="mt-1 text-xs text-[#713a35]">{{ form.errors.major }}</p>
                </div>
            </section>
            <section class="admin-panel grid content-start gap-4">
                <h2 class="admin-section-title">Foto profil</h2>
                <ImageUploadField id="photo" v-model="form.photo" compact label="Pilih foto" hint="JPG, PNG, atau WebP · maks. 8 MB." :preview-url="member?.photo_url" :error="form.errors.photo" :alt="member?.name || 'Pratinjau foto anggota'" />
                <ImageUploadField
                    id="photo_cutout"
                    v-model="form.photo_cutout"
                    label="Foto tanpa latar (opsional, untuk efek poster)"
                    hint="PNG atau WebP transparan · maks. 5 MB."
                    accept="image/png,image/webp"
                    :preview-url="member?.photo_cutout_url"
                    :preview-class="'bg-[conic-gradient(#4a4a4a_25%,#242424_0_50%,#4a4a4a_0_75%,#242424_0)] bg-[length:24px_24px]'"
                    :error="form.errors.photo_cutout"
                    :alt="`${member?.name || 'Anggota'} · foto tanpa latar`"
                />
                <p v-if="!member?.photo_cutout_url && !form.photo_cutout" class="text-xs font-medium text-[#713a35]">FOTO TANPA LATAR BELUM DIUNGGAH</p>
            </section>
            <section class="admin-panel grid gap-5 md:col-span-2 sm:grid-cols-2">
                <h2 class="admin-section-title sm:col-span-2">Cerita & publikasi</h2>
                <div class="sm:col-span-2"><label class="admin-label" for="quote">Kutipan</label><textarea id="quote" v-model="form.quote" class="admin-input min-h-24"></textarea></div>
                <div class="sm:col-span-2"><label class="admin-label" for="bio">Biografi</label><textarea id="bio" v-model="form.bio" class="admin-input min-h-28"></textarea></div>
                <div><label class="admin-label" for="instagram">Tautan Instagram</label><input id="instagram" v-model="form.instagram" class="admin-input" type="url"></div>
                <div><label class="admin-label" for="sort_order">Urutan tampil</label><input id="sort_order" v-model.number="form.sort_order" class="admin-input" type="number" min="0" required></div>
                <label class="flex min-h-11 items-center gap-3 border-t border-[#d8d1c6] pt-4 text-sm sm:col-span-2"><input v-model="form.status" type="checkbox" class="size-4 accent-[#73552f]"> Tampilkan profil di website</label>
            </section>
            <div class="admin-form-actions md:col-span-2">
                <div v-if="form.progress" class="flex basis-full items-center gap-3 text-xs text-[#55554f]" role="status" aria-live="polite"><span>Unggah {{ form.progress.percentage }}%</span><progress class="h-1 min-w-24 flex-1 accent-[#8b4b45]" :value="form.progress.percentage" max="100" aria-label="Progres unggah foto anggota" /></div>
                <Link href="/admin/anggota" class="admin-button-secondary focus-ring">Batal</Link>
                <button class="admin-button-primary focus-ring" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan anggota' }}</button>
            </div>
        </form>
    </AdminLayout>
</template>