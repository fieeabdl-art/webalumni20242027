<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import AutoCutout from '../../../Components/AutoCutout.vue';
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
    remove_photo: false, remove_photo_cutout: false,
});
const originalPreviewUrl = ref('');
const cutoutPreviewUrl = ref('');
const cutoutProcessing = ref(false);
let cutoutSelectionGeneration = 0;

function replaceObjectUrl(target, file) {
    if (target.value) {
        URL.revokeObjectURL(target.value);
    }

    target.value = file ? URL.createObjectURL(file) : '';
}

watch(() => form.photo, (file) => replaceObjectUrl(originalPreviewUrl, file));
watch(() => form.photo_cutout, (file) => replaceObjectUrl(cutoutPreviewUrl, file));

const posterPreview = computed(() => {
    const photoUrl = form.remove_photo ? null : originalPreviewUrl.value || props.member?.original_url || props.member?.photo_url || null;
    const cutoutUrl = form.remove_photo_cutout ? null : cutoutPreviewUrl.value || props.member?.photo_cutout_url || null;

    return {
        name: form.name || 'DATA BELUM TERSEDIA',
        nickname: form.nickname.trim(),
        class_name: form.class_name,
        major: form.major,
        photo_url: photoUrl,
        photo_cutout_url: cutoutUrl,
        poster_url: cutoutUrl || photoUrl,
        poster_uses_cutout: Boolean(cutoutUrl),
        quote: form.quote,
        instagram: form.instagram,
    };
});

function selectOriginal(file) {
    form.photo = file;
    if (file) {
        form.remove_photo = false;
    }
}

function createImageBlob(canvas, type, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
                return;
            }

            reject(new Error('Browser tidak dapat menyiapkan berkas cutout.'));
        }, type, quality);
    });
}

async function selectCutout(file) {
    const operation = cutoutSelectionGeneration + 1;
    cutoutSelectionGeneration = operation;
    form.clearErrors('photo_cutout');

    if (!file) {
        cutoutProcessing.value = false;
        form.photo_cutout = null;
        return;
    }

    cutoutProcessing.value = true;

    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, 1200 / Math.max(bitmap.width, bitmap.height));

        if (scale === 1 && file.size <= 5 * 1024 * 1024) {
            bitmap.close();
            if (operation === cutoutSelectionGeneration) {
                form.photo_cutout = file;
                form.remove_photo_cutout = false;
            }
            return;
        }

        const width = Math.max(1, Math.round(bitmap.width * scale));
        const height = Math.max(1, Math.round(bitmap.height * scale));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d');

        if (!context) {
            bitmap.close();
            throw new Error('Canvas tidak tersedia untuk menyiapkan cutout.');
        }

        context.drawImage(bitmap, 0, 0, width, height);
        bitmap.close();
        let blob = await createImageBlob(canvas, 'image/webp', 0.86);

        if (blob.type !== 'image/webp') {
            blob = await createImageBlob(canvas, 'image/png');
        }

        if (blob.size > 5 * 1024 * 1024) {
            throw new Error('Cutout hasil optimasi masih lebih dari 5 MB.');
        }

        if (operation === cutoutSelectionGeneration) {
            const baseName = file.name.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_-]/g, '-');
            const extension = blob.type === 'image/webp' ? 'webp' : 'png';
            form.photo_cutout = new File([blob], `${baseName}-poster.${extension}`, { type: blob.type });
            form.remove_photo_cutout = false;
        }
    } catch (error) {
        if (operation === cutoutSelectionGeneration) {
            form.photo_cutout = null;
            form.setError('photo_cutout', error instanceof Error ? error.message : 'Cutout tidak dapat disiapkan.');
        }
    } finally {
        if (operation === cutoutSelectionGeneration) {
            cutoutProcessing.value = false;
        }
    }
}

const submit = () => form.transform((data) => ({ ...data, status: data.status ? 1 : 0 })).post(
    props.member ? `/admin/anggota/${props.member.id}?_method=PUT` : '/admin/anggota',
    { forceFormData: true },
);

onBeforeUnmount(() => {
    cutoutSelectionGeneration += 1;

    if (originalPreviewUrl.value) {
        URL.revokeObjectURL(originalPreviewUrl.value);
    }

    if (cutoutPreviewUrl.value) {
        URL.revokeObjectURL(cutoutPreviewUrl.value);
    }
});
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
                <h2 class="admin-section-title">Foto asli (tampil saat poster diklik)</h2>
                <ImageUploadField
                    id="photo"
                    :model-value="form.photo"
                    label="Unggah foto asli"
                    hint="JPG, PNG, atau WebP · maks. 5 MB."
                    :preview-url="form.remove_photo ? '' : member?.original_url || member?.photo_url || ''"
                    :error="form.errors.photo"
                    :alt="`Foto ${member?.name || 'anggota'}`"
                    @update:model-value="selectOriginal"
                />
                <button
                    v-if="(member?.original_url && !form.photo) || form.remove_photo"
                    type="button"
                    class="focus-ring w-fit text-[10px] font-semibold uppercase tracking-[0.12em] text-[#8b4b45]"
                    @click="form.remove_photo = !form.remove_photo"
                >
                    {{ form.remove_photo ? 'Batalkan hapus foto asli' : 'Hapus foto asli' }}
                </button>
            </section>
            <section class="admin-panel grid content-start gap-4">
                <h2 class="admin-section-title">Foto tanpa latar (tampil di poster)</h2>
                <ImageUploadField
                    id="photo_cutout"
                    :model-value="form.photo_cutout"
                    label="Unggah manual · opsional"
                    hint="PNG atau WebP transparan · maks. 1200 px per sisi, 5 MB."
                    accept="image/png,image/webp"
                    :preview-url="form.remove_photo_cutout ? '' : member?.photo_cutout_url || ''"
                    preview-class="bg-[conic-gradient(#4a4a4a_25%,#242424_0_50%,#4a4a4a_0_75%,#242424_0)] bg-[length:24px_24px]"
                    :error="form.errors.photo_cutout"
                    :alt="`${member?.name || 'Anggota'} · foto tanpa latar`"
                    @update:model-value="selectCutout"
                />
                <AutoCutout
                    :source-file="form.photo"
                    :source-url="form.remove_photo ? '' : member?.original_url || member?.photo_url || ''"
                    :selected-cutout="form.photo_cutout"
                    @cutout="selectCutout"
                />
                <button
                    v-if="(member?.photo_cutout_url && !form.photo_cutout) || form.remove_photo_cutout"
                    type="button"
                    class="focus-ring w-fit text-[10px] font-semibold uppercase tracking-[0.12em] text-[#8b4b45]"
                    @click="form.remove_photo_cutout = !form.remove_photo_cutout"
                >
                    {{ form.remove_photo_cutout ? 'Batalkan hapus cutout' : 'HAPUS CUTOUT' }}
                </button>
                <p v-if="!member?.photo_cutout_url && !form.photo_cutout" class="text-xs font-medium text-[#713a35]">FOTO TANPA LATAR BELUM DIUNGGAH</p>
                <div class="mx-auto w-full max-w-52" role="img" :aria-label="`Pratinjau poster ${posterPreview.name}`">
                    <div class="relative isolate aspect-[3/4] overflow-hidden bg-[radial-gradient(ellipse_at_50%_100%,#3a1512,#111_70%)] text-[#f5f2ec]">
                        <span class="absolute inset-x-2 top-[27%] z-[1] text-center font-editorial text-[clamp(1.5rem,8vw,3rem)] font-black uppercase leading-[0.8]">{{ posterPreview.nickname || posterPreview.name.split(/\s+/)[0] }}</span>
                        <img
                            v-if="posterPreview.poster_url"
                            :src="posterPreview.poster_url"
                            :alt="`Pratinjau foto poster ${posterPreview.name}`"
                            class="absolute inset-x-[5%] bottom-0 z-[2] h-[78%] w-[90%]"
                            :class="posterPreview.poster_uses_cutout ? 'object-contain object-bottom' : 'object-cover object-[50%_25%]'"
                        >
                        <span v-else class="absolute inset-x-2 bottom-[18%] z-[2] text-center text-[9px] font-semibold uppercase tracking-[0.12em] text-white/80">DATA BELUM TERSEDIA</span>
                        <span class="absolute inset-x-2 bottom-2 z-[3] text-center text-[8px] uppercase tracking-[0.12em]">{{ posterPreview.major || 'DATA BELUM TERSEDIA' }}</span>
                    </div>
                </div>
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
                <p v-if="cutoutProcessing" class="basis-full text-xs text-[#55514b]" role="status" aria-live="polite">Menyiapkan cutout maksimal 1200 px…</p>
                <div v-if="form.progress" class="flex basis-full items-center gap-3 text-xs text-[#55554f]" role="status" aria-live="polite"><span>Unggah {{ form.progress.percentage }}%</span><progress class="h-1 min-w-24 flex-1 accent-[#8b4b45]" :value="form.progress.percentage" max="100" aria-label="Progres unggah foto anggota" /></div>
                <Link href="/admin/anggota" class="admin-button-secondary focus-ring">Batal</Link>
                <button class="admin-button-primary focus-ring" :disabled="form.processing || cutoutProcessing">{{ form.processing ? 'Menyimpan…' : 'Simpan anggota' }}</button>
            </div>
        </form>
    </AdminLayout>
</template>