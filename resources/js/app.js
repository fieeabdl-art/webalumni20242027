import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

const pages = import.meta.glob('./Pages/**/*.vue');

createInertiaApp({
	title: (title) => title ? `${title} | Arsip Angkatan` : 'Arsip Angkatan',
	resolve: (name) => {
		const page = pages[`./Pages/${name}.vue`];

		if (!page) {
			throw new Error(`Halaman Inertia "${name}" tidak ditemukan.`);
		}

		return page();
	},
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.mount(el);
	},
	progress: {
		color: '#A38B68',
	},
	defaults: {
		visitOptions: (href, options) => ({
			...options,
			viewTransition: options.method === 'get' && href.startsWith('/'),
		}),
	},
});
