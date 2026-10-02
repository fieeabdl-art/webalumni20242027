<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

const props = defineProps({ settings: { type: Object, default: () => ({}) } });
const form = useForm({
    site_name: props.settings.site_name || '', email: props.settings.email || '', instagram: props.settings.instagram || '',
    whatsapp: props.settings.whatsapp || '', footer_text: props.settings.footer_text || '', logo: null, favicon: null,
    preloader_enabled: props.settings.preloader_enabled !== '0' && props.settings.preloader_enabled !== false,
    custom_cursor_enabled: props.settings.custom_cursor_enabled !== '0' && props.settings.custom_cursor_enabled !== false,
});
const submit = () => form.put('/admin/pengaturan', { forceFormData: true });
</script>

<template>
    <AdminLayout title="Pengaturan">
        <div><p class="text-[9px] uppercase tracking-[0.16em] text-[#a38b68]">Identitas website</p><h1 class="mt-2 font-editorial text-4xl">Pengaturan</h1></div>
        <form class="mt-8 grid max-w-4xl gap-5 border-t border-black/15 pt-6 sm:grid-cols-2" @submit.prevent="submit">
            <div class="sm:col-span-2"><label class="admin-label" for="site_name">Nama website</label><input id="site_name" v-model="form.site_name" class="admin-input" required></div>
            <div><label class="admin-label" for="email">Email</label><input id="email" v-model="form.email" class="admin-input" type="email"></div>
            <div><label class="admin-label" for="instagram">Instagram URL</label><input id="instagram" v-model="form.instagram" class="admin-input" type="url" placeholder="https://instagram.com/…"></div>
            <div><label class="admin-label" for="whatsapp">WhatsApp</label><input id="whatsapp" v-model="form.whatsapp" class="admin-input"></div>
            <div><label class="admin-label" for="footer_text">Teks footer</label><input id="footer_text" v-model="form.footer_text" class="admin-input"></div>
            <div><label class="admin-label" for="logo">Logo</label><input id="logo" class="admin-input" type="file" accept="image/jpeg,image/png,image/webp" @input="form.logo = $event.target.files[0]"></div>
            <div><label class="admin-label" for="favicon">Favicon (.ico, PNG, WebP)</label><input id="favicon" class="admin-input" type="file" accept="image/x-icon,image/png,image/webp" @input="form.favicon = $event.target.files[0]"></div>
            <fieldset class="grid gap-4 border-t border-black/15 pt-5 sm:col-span-2">
                <legend class="admin-label">Preferensi gerak</legend>
                <label class="flex items-start gap-3 text-xs leading-6">
                    <input v-model="form.preloader_enabled" type="checkbox" class="mt-1 accent-[#8b4b45]">
                    <span><strong class="font-semibold">Preloader sinematik</strong><span class="block text-[#6b6b65]">Tampilkan sekali pada kunjungan pertama di setiap sesi browser.</span></span>
                </label>
                <label class="flex items-start gap-3 text-xs leading-6">
                    <input v-model="form.custom_cursor_enabled" type="checkbox" class="mt-1 accent-[#8b4b45]">
                    <span><strong class="font-semibold">Cursor khusus desktop</strong><span class="block text-[#6b6b65]">Nonaktif otomatis pada perangkat sentuh dan saat reduced motion.</span></span>
                </label>
            </fieldset>
            <div class="sm:col-span-2"><button class="focus-ring min-h-11 bg-[#242424] px-5 text-[9px] font-semibold uppercase tracking-[0.15em] text-white" :disabled="form.processing">Simpan pengaturan</button></div>
        </form>
    </AdminLayout>
</template>