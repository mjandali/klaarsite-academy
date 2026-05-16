import './bootstrap';
import '../css/app.css';
import 'vue3-toastify/dist/index.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';
import Vue3Toastify from 'vue3-toastify';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Klaarsite Academy';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(Vue3Toastify, {
                autoClose: 4000,
                clearOnUrlChange: false,
                multiple: true,
                newestOnTop: true,
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
