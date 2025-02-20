var project_info = document.project_info || {};
project_info = (function (){
    return {
        start(PHPPARAMS){

            const projectinfoApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        PROJECT_ID: PHPPARAMS.PROJECT_ID,
                        showEditTitle: false,
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
                    project(){
                        for (p of this.data){
                            if (p.ID == this.PROJECT_ID) return p;
                        }
                    },
                },
                methods: {
                    ...BX.Vue3.Pinia.mapActions(brieflistStore, ['getRequireFields']),
                    projectStatus(){
                        let all = 0;
                        let stoped = 0;
                        let worked = 0;
                        let planed = 0;
                        let complited = 0;
                        for(task of this.datatask){
                            if (task.UF_PROJECT_ID == this.project.ID){
                                for(queue of this.datacalendarQueue){
                                    if(queue.UF_TASK_ID == task.ID){
                                        all++;
                                        if (queue.UF_STATUS == 10 || queue.UF_STATUS == 0 || queue.UF_STATUS == 9) complited++;
                                        if (queue.UF_STATUS == 0) planed++;
                                        if (queue.UF_STATUS != 10 && queue.UF_STATUS != 0 && queue.UF_STATUS != 9) worked++;
                                        if (queue.UF_STATUS == 10) stoped++;
                                    }
                                }
                            }
                        }


                        if (!all) return "";
                        if (worked > 0) return '<div class="text-success">Выполняется</div>';
                        if (stoped = all) return '<div class="text-secondary">Остановлена</div>';
                        if (planed = all) return '<div class="text-warning">Запустится автоматически</div>';
                        if (complited == all) return '<div class="text-secondary">Завершена</div>';

                        return '';
                    },
                    saveinput(){
                      if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{
                          this.save();
                      },2000)
                    },
                    save(){
                        this.showEditTitle= false;

                        var cur = this;
                        kabinet.loading();

                        var form_data = new FormData();
                        for ( var key in cur.project ) {
                            if (Array.isArray(cur.project[key])){
                                cur.project[key].forEach(function (item,index) {
                                    if (typeof item.VALUE != "undefined") form_data.append(key + '[]', item.VALUE);
                                    else form_data.append(key + '[]', item);
                                });
                            }else
                                form_data.append(key, cur.project[key]);
                        }

                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.briefevents.editproject', {
                            data : form_data,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const}
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                const data = response.data;
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                let project = cur.project;
                                for(name in data.fields) project[name] = data.fields[name];

                                //console.log(response)
                                kabinet.loading(false);
                            }, function (response) {
                                //console.log(response);
                                kabinet.loading(false);
                                response.errors.forEach((error) => {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = error.message;
                                });

                            });
                    },
                },
                created(){
                },
                mounted() {
                },
                // language=Vue
                template: '#project-info'
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
            projectinfoApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            projectinfoApplication.config.globalProperties.$id = getId;

            projectinfoApplication.use(store);
            projectinfoApplication.mount('#projectinfocontent');
        }
    }
}());