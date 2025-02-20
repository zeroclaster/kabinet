var project_detail = document.project_detail || {};
project_detail = (function (){
    return {
        start(PHPPARAMS){

            const projectdetailApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
						PROJECT_ID: PHPPARAMS.PHPPARAMS,
                    }
                },
                computed: {
					    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
						...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
                },
                methods: {
					...BX.Vue3.Pinia.mapActions(brieflistStore, ['getRequireFields']),
                },
                created(){
                },
                mounted() {
                },
                // language=Vue
                template: '#project-detail'
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
            projectdetailApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            projectdetailApplication.config.globalProperties.$id = getId;

            projectdetailApplication.use(store);
            projectdetailApplication.mount('#projectdetailcontent');
        }
    }
}());