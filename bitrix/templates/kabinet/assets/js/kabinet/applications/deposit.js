var deposit_form = document.deposit_form || {};
deposit_form = (function (){
    return {
        start(PHPPARAMS){

            // Получаем хранилище до создания приложения
            const agreementStore = AgreementStore();

            // Определяем тип оплаты по умолчанию на основе contracttype
            let defaultTypePay = PHPPARAMS.TYPEPAY;
            if (!agreementStore.contracttype.value || agreementStore.contracttype.value == "0" || agreementStore.contracttype.value == "1") {
                defaultTypePay = 1; // Карта
            } else if (agreementStore.contracttype.value > 1) {
                defaultTypePay = 3; // Банковский перевод
            }


            const depositApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        fields :{
                            summapopolneniya:0,	
							summapopolneniya2:0,
                            percentpopolneniya:0,
                            promocode:'',
                            typepay: defaultTypePay, // Используем вычисленное значение
                            qrsumm:PHPPARAMS.QRSUMM,
                        },
                        errorField:{},
                        pecent: [0,7,7,3],
                        // 2025-02-17 по ТЗ меняем  на такой расет
                        pecent2: [0,0.93,0.93,0.97],
                        sumpopolnenia:0,
                    }
                },
                computed: {
					...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                    ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
					...BX.Vue3.Pinia.mapState(AgreementStore, ['contract','bank','contracttype']),
                    totalsum(){
						const typepay = this.fields.typepay;
						
						
                        const pecent2 = this.pecent2[typepay];
						
						var summapopolneniya = 0;
						
						if (typepay == 1 || typepay == 3) summapopolneniya = this.fields.summapopolneniya;
						if (typepay == 2) summapopolneniya = this.fields.qrsumm;

									
                        summapopolneniya = parseFloat(summapopolneniya);
                        return parseFloat(summapopolneniya / pecent2).toFixed(2);
                    },					
                    isError(){
                        for(fieldName in this.errorField){
                            if (this.errorField[fieldName]) return true;
                        }

                        return false;
                    },
                },
                methods: {
                    showError(field){
                        if (typeof this.errorField[field] != "undefined" && this.errorField[field]) return true;

                        return false;
                    },
                    clearError(){
                        for(fieldName in this.errorField){
                            this.errorField[fieldName] = false;
                        }
                    },
                    onInput(){
                        this.clearError();
                    },
                    onInput2(){
                        this.clearError();

                        let summapopolneniya = parseInt(this.fields.summapopolneniya);
                        let percentpopolneniya = parseInt(this.fields.percentpopolneniya);

                        this.sumpopolnenia = summapopolneniya*percentpopolneniya/100;
                    },
                    onChange(){
                        this.clearError();

                        const el = document.querySelector("#toscroll");
                        if (el) el.scrollIntoView({behavior: 'smooth'});
                    },
                    toemail(e){
                        const form = e.target.form;
                        var formData = new FormData(form);
                        formData.append('sendemail',1);
                        const kabinetStore = usekabinetStore();

                        kabinet.loading();
                        BX.ajax({
                            url: '/ajax/pdfschot/',
                            data: formData,
                            method: 'POST',
                            dataType: 'json',
                            processData: false,
                            preparePost: false,
                            onsuccess: function(data) {
                                //console.log(data);
                                kabinet.loading(false);
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = "Счет успешно отправлен на Вашу почту!";
                            },
                            onfailure: function(data) {
                                //console.error(data)
                                kabinet.loading(false);
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = "Ошибка при отправке";
                            }
                        });

                    },
					download(e){

                        if (this.fields.summapopolneniya == 0)  this.errorField.summapopolneniya = true;
						if (this.fields.summapopolneniya < 1000)  this.errorField.summapopolneniya2 = true;                        

						if (this.contracttype.value == 0)  this.errorField.contractFieldEmpty = true;
						if (this.contracttype.value == 2 || this.contracttype.value == 3 || this.contracttype.value == 4){
							if (this.contract.UF_NAME == '')  this.errorField.contractFieldEmpty = true;
							if (this.contract.UF_UR_ADDRESS == '')  this.errorField.contractFieldEmpty = true;
							if (this.contract.UF_INN == '')  this.errorField.contractFieldEmpty = true;
							if ((this.contracttype.value == 3 || this.contracttype.value == 4) && this.contract.UF_KPP == '')  this.errorField.contractFieldEmpty = true;
							if (this.contract.UF_OGRN == '')  this.errorField.contractFieldEmpty = true;
						}
						
						if (this.contract.fio == '')  this.errorField.contractFieldEmpty = true;
						if (this.contract.act == '')  this.errorField.contractFieldEmpty = true;
						if (this.contract.mail_addres == '')  this.errorField.contractFieldEmpty = true;
						

														
                        if (this.isError){
							e.preventDefault();
							e.stopPropagation();
							return false;	
						}					
					},
                    onSubmit(e){

                        if (this.fields.summapopolneniya == 0)  this.errorField.summapopolneniya = true;
						if (this.fields.summapopolneniya < 1000)  this.errorField.summapopolneniya2 = true;                        

                        						
                        if (this.isError) return;

                        const form = document.querySelector("form[name='depositform1']");
                        kabinet.loading();
                        var formData = new FormData(form);
                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.bilingevents.makepaylink', {
                            data : formData,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const}
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                document.location.href = response.data.link;
                                kabinet.loading(false);
                                //console.log(data)
                            }, function (response) {
                                //console.log(response);
                                response.errors.forEach((error) => {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = error.message;
                                    kabinet.loading(false);
                                });

                            });

                        //e.preventDefault();
                       // e.stopPropagation();
                        //return false;
                    },
                    ondepositMoney(e){

                        const form = document.querySelector("form[name='depositform1']");
                        kabinet.loading();
                        var formData = new FormData(form);
                        formData.append('sumpopolnenia',this.sumpopolnenia);
                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.bilingevents.depositmoney', {
                            data : formData,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const}
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                const data = response.data;
                                kabinet.loading(false);

                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                // Update user new billink
                                const billing = billingStore();
                                billing.databilling = data.billinkdata;

                                //console.log(data)
                            }, function (response) {
                                //console.log(response);
                                response.errors.forEach((error) => {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = error.message;
                                    kabinet.loading(false);
                                });

                            });

                        //e.preventDefault();
                        // e.stopPropagation();
                        //return false;
                    },
                },
                mounted() {
                },
                // language=Vue
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
            depositApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            depositApplication.config.globalProperties.$id = getId;

            depositApplication.use(store);
            depositApplication.mount(PHPPARAMS.CONTAINER);
        }
    }
}());