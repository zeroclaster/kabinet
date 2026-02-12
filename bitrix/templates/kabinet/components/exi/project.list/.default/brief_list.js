var project_list = document.project_list || {};
project_list = (function (){
    return {
        start(PHPPARAMS){

            if (typeof PHPPARAMS.PROJECT_ID == "undefined") PHPPARAMS.PROJECT_ID = 0;

            const briefApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        PROJECT_ID: PHPPARAMS.PROJECT_ID,
                        modaldata: {title:'Добавить услугу',order:0,project:0},
                        modal2data: {title:'Удалить услугу',message:'',question:'Вы действительно хотите удалить?',basketitem:0,order_id:0},
                        myModal:{},
                        myModal2:{},
						usr_id_const: usr_id_const ? '&usr=' + usr_id_const : '',
						usr_id_const2: usr_id_const ? '?usr=' + usr_id_const : '',
                    }
                },
                setup(){
                    const {taskStatus_m,taskStatus_v,taskStatus_b} = task_status(PHPPARAMS);

                    return {
                        taskStatus_m,
                        taskStatus_v,
                        taskStatus_b
                    };
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
                    ...BX.Vue3.Pinia.mapState(cataloglistStore, ['data3','message']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask','testdata','gettestdataID']),
                    ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
                    ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                    project(){
                        if (!PHPPARAMS.PROJECT_ID) return [];
                        for (p of this.data){
                            if (p.ID == this.PROJECT_ID) return p;
                        }
                    },
                    futureSpending(){
                        return PHPPARAMS.FUTURE_SPENDING;
                    },

                },
                methods: {
                    ...helperVueComponents(),
                    ...BX.Vue3.Pinia.mapActions(brieflistStore, ['getRequireFields']),
                    aaa(){
                        this.testdata[0].TITLE='33333';
                        var t =this.gettestdataID();
                        const store = tasklistStore(); // Получаем экземпляр хранилища
                        const taskID = store.gettestdataID(1); // Вызываем геттер
                        console.log(taskID.TITLE);
                    },
                    getTaskID(PROJECT_ID,PRODUKT_ID){
						for(task of this.datatask){						
                            if (task.UF_PROJECT_ID == PROJECT_ID && task.UF_PRODUKT_ID == PRODUKT_ID) {					
								return task.ID;
							}	
                        }
					},
                    lastMonthExpenses(PROJECT_ID){
                        for(element of PHPPARAMS.LAST_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return parseInt(element.VALUE);
                        }
                    },
                    lastMonthExpensesMonth(PROJECT_ID){
                        for(element of PHPPARAMS.LAST_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return element.MONTH;
                        }
                    },
                    actualMonthExpenses(PROJECT_ID){
                        for(element of PHPPARAMS.ACTUAL_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return parseInt(element.VALUE);
                        }
                    },
                    actualMonthBudget(PROJECT_ID){
                        for(element of PHPPARAMS.ACTUAL_MONTH_BUDGET){
                            if (element.PROJECT_ID == PROJECT_ID) return parseInt(element.VALUE);
                        }
                    },
                    actualMonthExpensesMonth(PROJECT_ID){
                        for(element of PHPPARAMS.ACTUAL_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return element.MONTH;
                        }
                    },
                    nextMonthExpenses(PROJECT_ID){
                        for(element of PHPPARAMS.NEXT_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return parseInt(element.VALUE);
                        }
                    },
                    nextMonthExpensesDate(PROJECT_ID){
                        for(element of PHPPARAMS.NEXT_MONTH_EXPENSES){
                            if (element.PROJECT_ID == PROJECT_ID) return 'с '+element.MONTH_START + ' по '+ element.MONTH_END;
                        }
                    },
                    projectStatus(project) {
                        const task = tasklistStore();
                        const tasklist = task.getTaskByProjectId(project.ID);

                        // Если нет задач - считаем проект остановленным
                        if (tasklist.length === 0) {
                            return '<div class="text-secondary">Остановлена</div>';
                        }

                        let hasWork = false;
                        let allStopwark = true;
                        let allEndwork = true;

                        // Анализируем все задачи проекта
                        tasklist.forEach(val => {
                            const s = this.taskStatus_v(val.ID);

                            if (s.work > 0) hasWork = true;
                            if (s.stopwark <= 0) allStopwark = false;
                            if (s.endwork <= 0) allEndwork = false;
                        });

                        // Определяем статус по приоритетам
                        if (hasWork) {
                            return '<div class="text-success">Выполняется</div>';
                        }
                        if (allStopwark) {
                            return '<div class="text-warning">Запустится автоматически</div>';
                        }
                        if (allEndwork) {
                            return '<div class="text-secondary">Завершена</div>';
                        }

                        return '<div class="text-secondary">Остановлена</div>';
                    },
                    /*Требует вашего внимания*/
                    alertcount(PROJECT_ID){
                        // alert_project_count задается в bitrix/templates/kabinet/components/exi/project.list/.default/template.php
                        // создается в doitAction() bitrix/components/exi/project.list/class.php
                        return alert_project_count[PROJECT_ID] || 0;
                    },
                    showAlertCounter(task_id){
                        return task_alert[task_id] || '';
                    },
                    closemodal:function(){
                        this.$root.myModal.hide();
                    },
                    closemodal2:function(){
                        this.$root.myModal2.hide();
                    },
                    addbuttorder: function (project) {

                        this.modaldata.project = project.ID;

                        if (project.UF_ORDER_ID) {
                            this.modaldata.order = project.UF_ORDER_ID;
                        }

                        //this.modaldata.title = item.ID;
                        this.$root.myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {});
                        this.$root.myModal.show();
                    },
                    removeProductModal:function (product){

                        this.modal2data.basketitem = product.BASKET_ID;
                        this.modal2data.order_id = product.ORDER_ID;
                        this.modal2data.message = '';

                        //this.modaldata.title = item.ID;
                        this.$root.myModal2 = new bootstrap.Modal(document.getElementById('exampleModal2'), {});
                        this.$root.myModal2.show();
                    },
                    increment:function (product){					
                        if (product.MAXIMUM_QUANTITY_MONTH>0 && parseInt(product.COUNT) > product.MAXIMUM_QUANTITY_MONTH) {
                            const kabinetStore = usekabinetStore();
                            kabinetStore.Notify = '';
                            kabinetStore.Notify = this.message.error1;
                            return ;
                        }
                        product.COUNT = parseInt(product.COUNT) + 1;
                    },
                    decrease:function (product){
		                if (product.MINIMUM_QUANTITY_MONTH>0 && parseInt(product.COUNT) <= product.MINIMUM_QUANTITY_MONTH) {
                            const kabinetStore = usekabinetStore();
                            kabinetStore.Notify = '';
                            kabinetStore.Notify = this.message.error3;
                            return ;
                        }				
												
                        if (parseInt(product.COUNT) == 0) return ;
                        product.COUNT = parseInt(product.COUNT) - 1;
                    },
                    chooseadd: function(product){					
                        if (parseInt(product.COUNT) == 0) {
                            const kabinetStore = usekabinetStore();
                            kabinetStore.Notify = '';
                            kabinetStore.Notify = this.message.error2;
                            return ;
                        }

                        this.addproduct(product.ID, product.COUNT,this.modaldata.order,this.modaldata.project);
                    },
                    removeproduct: function (BASKET_ID,ORDER_ID){
                        this.removeproductorder(BASKET_ID, ORDER_ID,this.modal2data);

                    },
                    addproduct(ID,COUNT,ORDER_ID,PROJECT_ID){                   
                        kabinet.loading();
                        var cur = this;

                        let formData = new FormData();
                        formData.append('id', ID);
                        formData.append('count', COUNT);
                        formData.append('order_id', ORDER_ID);
                        formData.append('project_id', PROJECT_ID);
                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.briefevents.addproduct', {
                            data : formData,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const}
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                const data = response.data;
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                for(name in data.data) cur.data[name] = data.data[name];
                                for(name in data.data2) cur.data2[name] = data.data2[name];
                                kabinet.loading(false);
								
								// закрываем окно добавления новой задачи
								cur.closemodal();
								//window.open('https://kupi-otziv.ru/kabinet/projects/planning/?p='+PROJECT_ID+'#produkt'+ID, '_blank');
								window.document.location.href = 'https://kupi-otziv.ru/kabinet/projects/planning/?p='+PROJECT_ID+'#produkt'+ID;
								
                                //console.log(data)
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
                    removeproductorder(ID,ORDER_ID,modal){
                        var cur = this;

                        let formData = new FormData();
                        formData.append('id', ID);
                        formData.append('order_id', ORDER_ID);
                        const kabinetStore = usekabinetStore();
                        BX.ajax.runAction('bitrix:kabinet.evn.briefevents.removeproduct', {
                            data : formData,
                            // usr_id_const нужен для админа, задается в footer.php
                            getParameters: {usr : usr_id_const}
                            //processData: false,
                            //preparePost: false
                        })
                            .then(function(response) {
                                const data = response.data;
                                //kabinetStore.NotifyOk = '';
                                //kabinetStore.NotifyOk = data.message;
                                modal.message = data.message;

                                for(name in data.data) cur.data[name] = data.data[name];
                                for(name in data.data2) cur.data2[name] = data.data2[name];
                                //console.log(data)
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
                    hasTasksInProject(projectId) {
                        return this.datatask.some(task => task.UF_PROJECT_ID === projectId);
                    }
                },
                components: {
                    messangerperformances___,
                },
                mounted() {
                },
                    // language=Vue
                template: PHPPARAMS.TEMPLATE
            });

            configureVueApp(briefApplication,PHPPARAMS.CONTAINER);
        }
    }
}());