var dogovor_creator = document.dogovor_creator || {};
dogovor_creator = (function (){
    return {
        start(PHPPARAMS){

            const dogovorcreatorApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        err_message:'',
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(AgreementFormStore, ['fields','contractsettings','fields2','banksettings','contracttype']),
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                },
                methods: {
                    dowload(e){

                        if (this.err_message) {
                            e.stopPropagation();
                            e.preventDefault();
                            return false;
                        }
                    }
                },
                template: PHPPARAMS.TEMPLATE
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
            dogovorcreatorApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            dogovorcreatorApplication.config.globalProperties.$id = getId;

            dogovorcreatorApplication.use(store);
            dogovorcreatorApplication.mount(PHPPARAMS.CONTAINER);
        }
    }
}());