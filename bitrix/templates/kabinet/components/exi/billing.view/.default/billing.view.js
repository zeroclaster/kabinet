var billing_view = document.billing_view || {};
billing_view = (function (){
    return {
        start(PHPPARAMS){

            const EXPENSES_NEXT_MONTH = PHPPARAMS['EXPENSES_NEXT_MONTH'];

            const billingViewApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        showloadmore:true,
                        usr_id_const: '?usr='+usr_id_const,
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                    ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
                    ...BX.Vue3.Pinia.mapState(historylistStore, ['historybillingdata']),
                    viewedcount(){
                        return this.historybillingdata.length;
                    },
                    isAlertFinance(){
                        if (parseInt(this.databilling.UF_VALUE_ORIGINAL) < parseInt(EXPENSES_NEXT_MONTH)) return 'btn-danger';
                        return '';
                    }
                },
                methods: {
                    project(history){
                        if(!history.UF_PROJECT_ID) return false;

                        for(p of this.data)
                            if(p.ID == history.UF_PROJECT_ID) return p;

                        return false;
                    },
                    task(history){
                        if(!history.UF_TASK_ID) return false;

                        for(t of this.datatask)
                            if(t.ID == history.UF_TASK_ID) return t;

                        return false;
                    },
                    moreload:function (e) {
                        const this_ = this;
                        let formData = new FormData;
                        this.$root.offset = this.$root.offset + this_.countview;
                        formData.append("OFFSET",this.$root.offset);
                        formData.append("countview",this_.countview);
                        for (fieldname in PHPPARAMS.FILTER) formData.append(fieldname,filterclientlist[fieldname]);
                        const kabinetStore = usekabinetStore();
                        kabinet.loading();
                        var data = BX.ajax.runComponentAction("exi:billing.view", "loadmore", {
                            mode: 'class',
                            data: formData,
                            timeout: 300
                        }).then(function (response) {
                            kabinet.loading(false);
                            const data = response.data;

                            if (typeof data.HISTORY_DATA != "undefined" && data.HISTORY_DATA.length == 0) this_.showloadmore = false;
                            if (this_.historybillingdata.length == this_.total) this_.showloadmore = false;

                            // клиенты
                            if (typeof data.HISTORY_DATA != "undefined")
                                for(element of data.HISTORY_DATA) {
                                    this_.historybillingdata.push(element);
                                }
                        }, function (response) {
                            kabinet.loading(false);
                            if (response.errors[0].code != 0) {
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = response.errors[0].message;
                            }else {
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                            }
                        });

                        e.preventDefault();
                        return false;
                    },
                },
                created(){
                },
                mounted() {
                    var this_ = this;

                    this.$root.offset = 0;		
                    if(parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;

                },
                components: {
                },
                // language=Vue
                template: PHPPARAMS.TEMPLATE
            });

            configureVueApp(billingViewApplication,PHPPARAMS.CONTAINER);
        }
    }
}());