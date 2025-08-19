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

            configureVueApp(dogovorcreatorApplication,PHPPARAMS.CONTAINER);
        }
    }
}());