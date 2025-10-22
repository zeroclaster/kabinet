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
                            summapopolneniya:'',
							summapopolneniya2:'',
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

                        if (typepay == 1 || typepay == 3) {
                            // Если поле пустое, возвращаем 0
                            if (this.fields.summapopolneniya === '') {
                                return '0.00';
                            }
                            // Преобразуем строку с запятой в число
                            let summaValue = this.fields.summapopolneniya.toString().replace(',', '.');
                            summapopolneniya = parseFloat(summaValue) || 0;
                        }
                        if (typepay == 2) {
                            let qrsummValue = this.fields.qrsumm.toString().replace(',', '.');
                            summapopolneniya = parseFloat(qrsummValue) || 0;
                        }

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

                    formatCurrency(event, fieldName) {
                        this.clearError();

                        let input = event.target;
                        let value = input.value;

                        // Если поле пустое, оставляем пустым
                        if (value === '') {
                            this.fields[fieldName] = '';
                            return;
                        }

                        // Разрешаем цифры, точку и запятую
                        value = value.replace(/[^\d.,]/g, '');

                        // Заменяем запятую на точку для единообразия
                        value = value.replace(/,/g, '.');

                        // Удаляем все точки, кроме первой
                        let dotCount = (value.match(/\./g) || []).length;
                        if (dotCount > 1) {
                            value = value.replace(/\.+$/, ""); // Удаляем точки в конце
                            value = value.substring(0, value.indexOf('.')) +
                                value.substring(value.indexOf('.')).replace(/\./g, '');
                        }

                        // Ограничиваем копейки до 2 знаков после точки
                        if (value.includes('.')) {
                            let parts = value.split('.');
                            if (parts[1].length > 2) {
                                parts[1] = parts[1].substring(0, 2);
                                value = parts[0] + '.' + parts[1];
                            }
                        }

                        // Убеждаемся, что число не начинается с точки
                        if (value.startsWith('.')) {
                            value = '0' + value;
                        }

                        // Обновляем значение в модели
                        this.fields[fieldName] = value;

                        // Обновляем значение в input (на случай, если мы что-то изменили)
                        input.value = value;
                    },

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
                    onInput2__(){
                        this.clearError();

                        let summapopolneniya = parseInt(this.fields.summapopolneniya);
                        let percentpopolneniya = parseInt(this.fields.percentpopolneniya);

                        this.sumpopolnenia = summapopolneniya*percentpopolneniya/100;
                    },
                    onInput2(){
                        this.clearError();

                        // Если поле пустое, устанавливаем сумму пополнения в 0
                        let summaValue = this.fields.summapopolneniya === '' ? '0' : this.fields.summapopolneniya.toString().replace(',', '.');
                        let percentValue = this.fields.percentpopolneniya.toString().replace(',', '.');

                        let summapopolneniya = parseFloat(summaValue) || 0;
                        let percentpopolneniya = parseFloat(percentValue) || 0;

                        this.sumpopolnenia = (summapopolneniya * percentpopolneniya / 100).toFixed(2);
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

                        // Преобразуем строку в число, учитывая запятую
                        let summaValue = this.fields.summapopolneniya.toString().replace(',', '.');
                        let summa = parseFloat(summaValue) || 0;

                        if (this.fields.summapopolneniya === '' || summa == 0) this.errorField.summapopolneniya = true;
                        if (summa < 1000) this.errorField.summapopolneniya2 = true;

                        //if (this.fields.summapopolneniya == 0)  this.errorField.summapopolneniya = true;
						//if (this.fields.summapopolneniya < 1000)  this.errorField.summapopolneniya2 = true;

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

                        // Преобразуем строку в число, учитывая запятую
                        let summaValue = this.fields.summapopolneniya.toString().replace(',', '.');
                        let summa = parseFloat(summaValue) || 0;

                        if (this.fields.summapopolneniya === '' || summa == 0) this.errorField.summapopolneniya = true;
                        if (summa < 1000) this.errorField.summapopolneniya2 = true;

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