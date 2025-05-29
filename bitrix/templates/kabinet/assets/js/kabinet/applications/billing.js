window.addEventListener("components:ready", function(event) {
    const HeaderApplication = BX.Vue3.BitrixVue.createApp({
        data(){
            return {}
        },
        computed: {
            ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
        },
        template: `
          <a href="/kabinet/finance/" target="_blank">Баланс: {{databilling.UF_VALUE}} руб.</a>
    `
    });

    HeaderApplication.use(store);
    HeaderApplication.mount('#headderapp');
});