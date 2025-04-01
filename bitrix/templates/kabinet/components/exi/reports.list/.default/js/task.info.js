var task_info = document.task_info || {};
task_info = (function (){
    return {
        start(PHPPARAMS,signedParameters){
            const taskinfoApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        'TASK_ID': PHPPARAMS.TASK_ID
                    }
                },
                setup(){
                    const {countQueu,taskStatus_m,taskStatus_v,taskStatus_b} = task_status();

                    return {
                        taskStatus_m,
                        taskStatus_v
                    };
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner'])
                },
                methods: {
                    ...helperVueComponents()
                },
                created(){
                },
                // после отрисовки всех представлений
                mounted() {
                },
                // language=Vue
                template: '#task-info-template'
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
            taskinfoApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            taskinfoApplication.config.globalProperties.$id = getId;

            taskinfoApplication.use(store);
            taskinfoApplication.mount('#taskinfocontent');
        }
    }
}());