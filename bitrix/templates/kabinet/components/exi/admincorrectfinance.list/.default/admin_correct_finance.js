var admin_correct_finance = document.admin_correct_finance || {};
admin_correct_finance = (function (){
    return {
        start(PHPPARAMS){

            const correctFinance = BX.Vue3.BitrixVue.mutableComponent('correct Finance', {
                template: `
                        <div class="d-flex align-items-center">
                            <div style="width: 200px;">
                                <input type="text" class="form-control" placeholder="0" v-model="changevalue">
                            </div>
                            <div class=" ml-2"> руб.</div>
                            <button ref="plus" class="btn btn-primary ml-2" @click="plus">Увеличить</button>
                            <button ref="minus" class="btn btn-warning ml-2" @click="minus">Уменьшить</button>
                        </div>
                `,
                data(){
                    return{
                        'changevalue':0
                    }
                },
                props: ['modelValue','tindex'],
                watch:{
                    changevalue: {
                        handler(newval, oldVal) {
                            // Нельзя уменьшить нулевую стоимость
                            let calc = parseInt(this.modelValue) - parseInt(newval);
                            if (calc < 0) BX.adjust(this.$refs.minus, {props: {disabled: true}});
                            else  BX.adjust(this.$refs.minus, {props: {disabled: false}});
                        },
                        deep: true
                    }
                },
                methods: {
                    plus(){
                        this.$emit('update:modelValue', parseInt(this.modelValue) + parseInt(this.changevalue));
                        this.changevalue = 0;
                        this.saveRunner();
                    },
                    minus(){
                        let calc = parseInt(this.modelValue) - parseInt(this.changevalue);
                        if(calc >= 0) this.$emit('update:modelValue', parseInt(this.modelValue) - parseInt(this.changevalue));
                        this.changevalue = 0;
                        this.saveRunner();
                    },
                    saveRunner(){
                        if (typeof this.inpSaveTimer != 'undefined') clearTimeout(this.inpSaveTimer);
                        this.inpSaveTimer = setTimeout(()=>{this.$root.correctMoney(this.tindex);},1000);
                    }
                },
                mounted () {
                    // Нельзя уменьшить нулевую стоимость
                    let calc = parseInt(this.modelValue);
                    if (calc == 0) BX.adjust(this.$refs.minus, {props: {disabled: true}});
                }
            });


            const adminCorrectFinanceApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        showloadmore:true,
                        limitpics:5,
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(clientlistStore, ['dataclient']),
                    ...BX.Vue3.Pinia.mapState(projectlistStore, ['dataproject']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['dataorder']),
                    ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner']),
                    ...BX.Vue3.Pinia.mapState(cataloglistStore, ['data3']),
                    isViewMore(){
                        if(this.total <= this.countview || !this.showloadmore) return false;
                        return true;
                    },
                    viewedcount(){
                        return this.datarunner.length;
                    },
                },
                methods: {
                    ...helperVueComponents(),
                    moreload:function (e) {
                        const this_ = this;
                        let formData = new FormData;
                        this.$root.offset = this.$root.offset + 25;
                        formData.append("OFFSET",this.$root.offset);
                        for (fieldname in filterclientlist) formData.append(fieldname,filterclientlist[fieldname]);

                        formData.append("countview",this_.countview);
                        const kabinetStore = usekabinetStore();
                        kabinet.loading();
                        var data = BX.ajax.runComponentAction("exi:adminexecution.list", "loadmore", {
                            mode: 'class',
                            data: formData,
                            timeout: 300
                        }).then(function (response) {
                            kabinet.loading(false);
                            const data = response.data;

                            if (
                                typeof data.RUNNER_DATA != "undefined" &&
                                data.RUNNER_DATA.length == 0
                            )
                                this_.showloadmore = false;

                            if (this_.dataclient.length == this_.total) this_.showloadmore = false;
                            //if (Object.keys(data.RUNNER_DATA).length == this_.total) this_.showloadmore = false;


							if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0){
								const message_store = messageStore();
								for(index in data.MESSAGE_DATA)
                                    message_store.datamessage[index] = data.MESSAGE_DATA[index];

							}

                            // клиенты
                            if (typeof data.CLIENT_DATA != "undefined")
                                for(index in data.CLIENT_DATA) {
                                    this_.dataclient[index] = data.CLIENT_DATA[index];
                            }

                            //проекты
                            if (typeof data.PROJECT_DATA != "undefined")
                                for(index in data.PROJECT_DATA) {
                                    this_.dataproject[index] = data.PROJECT_DATA[index];
                            }

                            // задачи
                            if (typeof data.TASK_DATA != "undefined")
                                for(index in data.TASK_DATA) {
                                    this_.datatask[index] = data.TASK_DATA[index];
                            }

                            //заказы
                            if (typeof data.ORDER_DATA != "undefined")
                                for(index in data.ORDER_DATA) {
                                    this_.dataorder[index] = data.ORDER_DATA[index];
                            }

                            // исполнения
                            if (typeof data.RUNNER_DATA != "undefined"){
                                data.RUNNER_DATA.forEach((elm)=>{this_.datarunner.push(elm)});
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
                    showall: function (task) {
                        task.LIMIT = 1000;
                    },
                    inpsave(index){

                        if (typeof this.$root.inpSaveTimer != 'undefined') clearTimeout(this.$root.inpSaveTimer);
                        this.$root.inpSaveTimer = setTimeout(()=>{this.savetask(index);},1000);
                    },
                    correctMoney: function (index){
                        var cur = this;
                        var runner;

                        kabinet.loading();
                        runner = this.datarunner[index];

                        var form_data = new FormData();
                        for ( var key in runner ) {
                            if (key=="UF_PIC_REVIEW"){
                                //if(runner["UF_PIC_REVIEW"].length==0) form_data.append(key + '[]', 0);
                                for (const file of runner["UF_PIC_REVIEW"]) form_data.append(key + '[]', file);
                            }else{
                                if (Array.isArray(runner[key]))
                                    runner[key].forEach(function (item,index) {
                                        form_data.append(key + '[]', item.VALUE);
                                    });

                                else form_data.append(key, runner[key]);
                            }
                        }

                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.correctmoney', {
                            data : form_data,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const},
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                //console.log(response)
                                const data = response.data;
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                cur.datarunner[index] = data.runner;
                                kabinet.loading(false);
                            }, function (response) {
                                //console.log(response);
                                kabinet.loading(false);
                                response.errors.forEach((error) => {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = error.message;
                                });

                                // сбрасываем данные до сохранения
                                setTimeout(()=>cur.resetSave(index),500);

                            });
                    },
                    savetask: function (index){
                        var cur = this;
                        var runner;

                        kabinet.loading();
                        runner = this.datarunner[index];

                        var form_data = new FormData();
                        for ( var key in runner ) {
                            if (key=="UF_PIC_REVIEW"){
                                //if(runner["UF_PIC_REVIEW"].length==0) form_data.append(key + '[]', 0);
                                for (const file of runner["UF_PIC_REVIEW"]) form_data.append(key + '[]', file);
                            }else{
                                if (Array.isArray(runner[key]))
                                    runner[key].forEach(function (item,index) {
                                        form_data.append(key + '[]', item.VALUE);
                                    });

                                else form_data.append(key, runner[key]);
                            }
                        }

                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.edite', {
                            data : form_data,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const},
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                //console.log(response)
                                const data = response.data;
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                cur.datarunner[index] = data.runner;
                                kabinet.loading(false);
                            }, function (response) {
                                kabinet.loading(false);
                                if (response.errors[0].code != 0) {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = response.errors[0].message;
                                }else {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                                }

                                // сбрасываем данные до сохранения
                                setTimeout(()=>cur.resetSave(index),500);

                            });
                    },
                    resetSave(index){
                        var cur = this;
                        var runner;
                        //kabinet.loading();
                        runner = this.datarunner[index];
                        var form_data = new FormData();
                        form_data.append('ID', runner.ID);
                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.reset', {
                            data : form_data,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const},
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                //console.log(response)
                                const data = response.data;
                                //kabinetStore.NotifyOk = '';
                                //kabinetStore.NotifyOk = data.message;

                                cur.datarunner[index] = data.runner;
                                //kabinet.loading(false);
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
                    },
                    catalogItem(PRODUCT_ID){
                        for(element of this.data3){
                            if (element.ID == PRODUCT_ID) return element;
                        }
                    },
                },
                created(){
                },
                mounted() {
                    var cur = this;
                    this.$root.offset = 0;
                    if(parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;
                },
                components: {
                    correctFinance
                },
                // language=Vue
                template: '#kabinet-content'
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
            adminCorrectFinanceApplication.config.globalProperties.$href = function (indicator) {
                 return `#${getId.call(this, indicator)}` }

            adminCorrectFinanceApplication.config.globalProperties.$id = getId;

            adminCorrectFinanceApplication.use(store);
            adminCorrectFinanceApplication.mount('#kabinetcontent');
        }
    }
}());