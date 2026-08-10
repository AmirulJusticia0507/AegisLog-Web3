import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { WagmiPlugin } from '@wagmi/vue';
import { http, createConfig } from '@wagmi/core';
import { polygon, arbitrum, mainnet } from '@wagmi/core/chains';
import App from './App.vue';

const wagmiConfig = createConfig({
    chains: [polygon, arbitrum, mainnet],
    transports: {
        [polygon.id]: http(),
        [arbitrum.id]: http(),
        [mainnet.id]: http(),
    },
});

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App: Component, props, plugin }) {
        createApp({ render: () => h(Component, props) })
            .use(plugin)
            .use(createPinia())
            .use(WagmiPlugin, { config: wagmiConfig })
            .mount(el);
    },
    title: (title) => (title ? `${title} — AegisLog Web3` : 'AegisLog Web3'),
});
