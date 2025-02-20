import { BitrixVue } from 'ui.vue3';

BitrixVue.createApp({
    components: {
        Counter
    },
    template: `<Counter/>`
}).use(store).mount('#application2');