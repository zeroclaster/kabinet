var adminexecution_list = document.adminclient_list || {};
adminexecution_list = (function (){
    return {
        start(PHPPARAMS){
            const changestatus = BX.Vue3.BitrixVue.mutableComponent('change status', {
                template: `
                          <div class="mt-3" v-if="catalog.length>0">
                    <div class="h4">Сменить статус:</div>
                    <div class="form-group select-status" v-for="Status in catalog">
                        <div class="form-check">
                          <input @change="saveStatus" :name="$id('name')" class="form-check-input" :id="$id(Status.ID)" v-model="localModelValue" type="radio" :value="Status.ID">
                          <label class="form-check-label text-primary" :for="$id(Status.ID)">{{Status.TITLE}}</label>
                        </div>
                    </div>
                </div>
                `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId(),
                        localModelValue : 0
                    }
                },
                props: ['modelValue','tindex','catalog'],
                computed: {
                },
                mounted () {
                    // Add event handler
                    const this_ = this;

                    this.$.iid = function() {
                        return "uid-" + kabinet.uniqueId();
                    };
                },
                methods: {
                    makeUniqeId(){
                        return 'inpid'+kabinet.uniqueId();
                    },
                    saveStatus(){
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{
                            this.$root.datarunner[this.tindex].UF_STATUS = this.localModelValue;
                            this.$root.savetask(this.tindex);
                            this.localModelValue = 0;
                            },1000);
                    }
                }
            });


            const accountfield = BX.Vue3.BitrixVue.mutableComponent('account-field', {
                template: `
                            <div class="mb-3 form-group">
                <div><label class="" :for="'accaunt-execution'+id_input">Имя аккаунта</label></div>
                <div><input class="form-control" type="text" :id="'accaunt-execution'+id_input" v-model="accauntModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'login-execution'+id_input">Логин</label></div>
                <div><input class="form-control" type="text" :id="'login-execution'+id_input" v-model="loginModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'pass-execution'+id_input">Пароль</label></div>
                <div><input class="form-control" type="text" :id="'pass-execution'+id_input" v-model="passModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'ip-execution'+id_input">IP размещения</label></div>
                <div><input class="form-control" type="text" :id="'ip-execution'+id_input" v-model="ipModelvalue" @input="inpsave(tindex)"></div>
            </div>
                `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId(),
                    }
                },
                props: ['modelValue','tindex'],
                computed: {
                    accauntModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.accaunt;
                        },
                        set(newValue) {
                            this.$.fields.accaunt = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    loginModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.login;
                        },
                        set(newValue) {
                            this.$.fields.login = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    passModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.pass;
                        },
                        set(newValue) {
                            this.$.fields.pass = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    ipModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.ip;
                        },
                        set(newValue) {
                            this.$.fields.ip = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                },
                mounted () {
                    // Add event handler
                    const this_ = this;
                },
                methods: {
                    createfields(){
                        return {'accaunt':'','login':'','pass':'','ip':''};
                    },
                    inpsave(index){
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{this.$root.savetask(index);},1000);
                    }
                }
            });


            const adminClientListApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        showloadmore:true,
                        limitpics:5
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
                    showhidehistory(e){
                        const node = BX.findChild(e.target.parentNode,{class:'history-list'},true,false);
                        if (e.target.checked) BX.show(node);
                        else  BX.hide(node);
                    },
                    displayCurrentStatus(runner){
                        let ret = '';
                        for(element of runner.STATUSLIST) if (element.ID == runner.UF_STATUS) ret = element.TITLE
                        return ret;
                    },
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
                            //console.log(response);
                            response.errors.forEach((error) => {
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = error.message;
                            });
                        });

                        e.preventDefault();
                        return false;
                    },
                    showpiclimits: function (pics,taskindex){
                        let ret = [];
                        if (typeof this.datarunner[taskindex].LIMIT === 'undefined') this.datarunner[taskindex].LIMIT = this.limitpics;
                        pics.forEach((value,index) =>{
                            if (index<this.datarunner[taskindex].LIMIT) ret.push(value);
                        });

                        return ret;
                    },
                    showall: function (task) {
                        task.LIMIT = 1000;
                    },
                    addSelectedPhoto(index,array){
                        array.forEach((elm)=> {
                            this.datarunner[index].UF_PIC_REVIEW_DOUBLE.push({VALUE:elm});
                        });
                    },
                    removeimg(id_photo,index){
                        this.datarunner[index].UF_PIC_REVIEW_DELETE = id_photo;
                        this.savetask(index);
                    },
                    inpsave(index){

                        if (typeof this.$root.inpSaveTimer != 'undefined') clearTimeout(this.$root.inpSaveTimer);
                        this.$root.inpSaveTimer = setTimeout(()=>{this.savetask(index);},1000);
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
                                //console.log(response);
                                //kabinet.loading(false);
                                response.errors.forEach((error) => {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = error.message;
                                });

                            });
                    },
                    isViewReport(status){
                        if ([7,8,9,10].indexOf(parseInt(status)) != -1) return true;

                        return false;
                    },
                    alertStyle(status){
                        if ([0].indexOf(parseInt(status)) != -1) return 'alert-warning';
                        if ([1,2,3,4,5,6,7,8].indexOf(parseInt(status)) != -1) return 'alert-success';
                        if ([9].indexOf(parseInt(status)) != -1) return 'alert-dark';
                        if ([10].indexOf(parseInt(status)) != -1) return 'alert-danger';
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
                    mydatepicker,
                    mytypeahead,
                    sharephoto,
                    accountfield,
                    changestatus,
                    messangerperformances,
                    richtext,
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
            adminClientListApplication.config.globalProperties.$href = function (indicator) {
                 return `#${getId.call(this, indicator)}` }

            adminClientListApplication.config.globalProperties.$id = getId;

            adminClientListApplication.use(store);
            adminClientListApplication.mount('#kabinetcontent');
        }
    }
}());