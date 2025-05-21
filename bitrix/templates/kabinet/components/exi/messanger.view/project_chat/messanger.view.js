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
                created(){
                },
                mounted() {
                    var cur = this;
                    window.addEventListener("components:ready", function(event) {
                    });
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