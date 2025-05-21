var messanger_view = document.messanger_view || {};
messanger_view = (function (){
    return {
        start(PHPPARAMS){
            const messangerViewApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                },
                components: {
                    messangerperformances,
                },
                // language=Vue
                template: '#messangerviewtemolate'
            });
            configureVueApp(messangerViewApplication,'#messangerblock');
        }
    }
}());