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


            const componentCounters = new WeakMap()
            // The "this" object is the current component instance.
            const getId = function (indicator) {
                if (!componentCounters.has(this)) {
                    componentCounters.set(this, kabinet.uniqueId())
                }
                const componentCounter = componentCounters.get(this)
                return `uid-${componentCounter}` + (indicator ? `-${indicator}` : '')
            }
            messangerViewApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            messangerViewApplication.config.globalProperties.$id = getId;

            messangerViewApplication.use(store);
            messangerViewApplication.mount('#messangerblock');
        }
    }
}());