var adminclient_list = document.adminclient_list || {};
adminclient_list = (function (){
    return {
        start(PHPPARAMS){
            const adminClientListApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        showloadmore:true
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(clientlistStore, ['dataclient']),
                    ...BX.Vue3.Pinia.mapState(projectlistStore, ['dataproject']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['dataorder']),
                    ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner']),
                    isViewMore(){
                        if(this.total <= this.countview || !this.showloadmore) return false;
                        return true;
                    },
                    viewedcount(){
                        return this.dataclient.length;
                    }
                },
                methods: {
                    ...helperVueComponents(),
                    moreload:function (e) {
                        const this_ = this;
                        let formData = new FormData;
                        this.$root.offset = this.$root.offset + 2;
                        formData.append("OFFSET",this.$root.offset);
                        for (fieldname in filterclientlist) formData.append(fieldname,filterclientlist[fieldname]);

                        formData.append("countview",this_.countview);
                        const kabinetStore = usekabinetStore();
                        kabinet.loading();
                        var data = BX.ajax.runComponentAction("exi:adminclient.list", "loadmore", {
                            mode: 'class',
                            data: formData,
                            timeout: 300
                        }).then(function (response) {
                            kabinet.loading(false);
                            const data = response.data;

                            if (typeof data.CLIENT_DATA != "undefined" && data.CLIENT_DATA.length == 0) this_.showloadmore = false;

                            // клиенты
                            if (typeof data.CLIENT_DATA != "undefined") {
                                data.CLIENT_DATA.forEach(function (element) {this_.dataclient.push(element);});
                                if (data.CLIENT_DATA.length == this_.total) this_.showloadmore = false;
                            };

                            //проекты
                            if (typeof data.PROJECT_DATA != "undefined")
                                for(index in data.PROJECT_DATA) {
                                    this_.dataproject[index] = data.PROJECT_DATA[index];
                                };

                            // задачи
                            if (typeof data.TASK_DATA != "undefined")
                                for(index in data.TASK_DATA) {
                                    this_.datatask[index] = data.TASK_DATA[index];
                                };

                            //заказы
                            if (typeof data.ORDER_DATA != "undefined")
                                for(index in data.ORDER_DATA) {
                                    this_.dataorder[index] = data.ORDER_DATA[index];
                                };

                            // исполнения
                            if (typeof data.RUNNER_DATA != "undefined")
                                for(index in data.RUNNER_DATA) {
                                    this_.datarunner[index] = data.RUNNER_DATA[index];
                                };

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
                    gotocearchstatus(event){
                        event.target.form.submit();
                    },
                    getTaskByProject(project){

                    },
                    getClientExecution(id_client){
                        
                        let taskID = [];
                        if (typeof this.datatask[id_client] == 'undefined') return taskID;
                        for (index in this.datatask[id_client]){
                           taskID.push(this.datatask[id_client][index].ID);
                        }

                        let ret = [];
                        for(id of taskID){
                            if (typeof this.datarunner[id_client] == 'undefined') continue;
                            if (typeof this.datarunner[id_client][id] == 'undefined') continue;
                            for(exec of this.datarunner[id_client][id]){
                                ret.push(exec);
                            }
                        }

                        return ret;
                    },
                    getClientExecution2(id_client,taskID){

                        if (typeof this.datarunner[id_client][taskID] != 'undefined') return this.datarunner[id_client][taskID];
                        return [];
                    },
                    getExecutionStatusCount(id_client,id_status){
                        let count = 0;
                        let data = this.getClientExecution(id_client);

                        for (index in data){
                            if (data[index].UF_STATUS == id_status) count = count + 1;
                        }

                        return count;
                    },
                    getExecutionStatusCount2(id_client,taskID,id_status){

                        let count = 0;
                        let data = this.getClientExecution2(id_client,taskID);

                        for (index in data){
                            if (data[index].UF_STATUS == id_status) count = count + 1;
                        }

                        return count;
                    },
                    badTask(task_id,client_id,order_id,product_id){
                        if (typeof this.dataorder[client_id][order_id][product_id] == "undefined") {
                            console.log("Ошибка в задаче ("+task_id+")! В проекте нет услуги ("+product_id+") из задачи.");
                        }
                    },
                    log(vareble){
                        console.log(vareble)
                    },
                },
                created(){
                },
                mounted() {
                    var cur = this;
                    this.$root.offset = 0;
                    if(this.total <= this.countview) this.showloadmore = false;
                    window.addEventListener("components:ready", function(event) {
                    });
                },
                components: {
                },
                // language=Vue
                template: '#kabinet-content'
            });


            const statuscatalog = function () {
                return PHPPARAMS['statuslistdata']
            }

            adminClientListApplication.config.globalProperties.statusCatalog = statuscatalog;


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