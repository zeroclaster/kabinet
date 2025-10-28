var task_list = document.task_list || {};
task_list = (function (){
    return {
        start(PHPPARAMS){

            const allowable = [
                "ID",
                "UF_DATE_COMPLETION",
                "UF_DATE_COMPLETION_ORIGINAL",
                "FINALE_PRICE",
                "RUN_DATE",
                "UF_CYCLICALITY",
                "UF_CYCLICALITY_ORIGINAL",
                "UF_NUMBER_STARTS",
                "UF_NUMBER_STARTS_ORIGINAL",
                "UF_RUN_DATE",
                "UF_RUN_DATE_ORIGINAL",
                "UF_STATUS",
                "UF_STATUS_ORIGINAL",
                "UF_PROJECT_ID",
                "UF_PROJECT_ID_ORIGINAL",
                "UF_PRODUKT_ID",
                "UF_PRODUKT_ID_ORIGINAL"
            ];

            if (typeof PHPPARAMS.PROJECT_ID === "undefined" || PHPPARAMS.PROJECT_ID == '')
                throw "Field PROJECT_ID not found!";


// TODO убрать тестовые методы
const taskApplication = BX.Vue3.BitrixVue.createApp({
    data() {
        return {
            limitpics:5,
            project_id: PHPPARAMS.PROJECT_ID,
            modaldata: {title:'Добавьте услугу в проект',order:0,project:0},
            modal2data: {title:'Удалить услугу',message:'',question:'Вы действительно хотите удалить?',basketitem:0,order_id:0},
            myModal:{},
            myModal2:{},
            listprd: [],
            anim_counter: []
        }
    },
    setup(){

        const {projectOrder, projectTask} = data_helper();
        const {taskStatus_m,taskStatus_v,taskStatus_b} = task_status();
        const tasklistS = tasklistStore();
        const {makeData,canBeSaved_} = canbesaved__();
        makeData(tasklistS.datatask);
        const getmomment = ()=>moment();

        const getCopyTask = function (task) {
            const id = task['ID'];
            var finded = null;
            for(index in this.datataskCopy){
                if (this.datataskCopy[index].ID == id){
                    finded = this.datataskCopy[index];
                    break;
                }
            }

            return finded;
        }

        const makedatataskCopy = function (data){
            var t = JSON.parse(JSON.stringify(data));
            for (item of t) for (f in item) if (allowable.indexOf(f) == -1) delete item[f];

            return t;
        }

        const datataskCopy = BX.Vue3.ref(makedatataskCopy(tasklistS.datatask));

        /**
         * Проверяет, является ли поле обязательным и пустым
         * @param {Object} task - Объект с данными задачи
         * @param {string} field_name - Имя проверяемого поля
         * @returns {boolean} Возвращает true если поле пустое (невалидное), false если поле заполнено (валидное)
         */
        const is_required_field = function(task, field_name) {
            const fieldValue = task[field_name];

            // Проверка на null/undefined
            if (fieldValue == null) {
                return true;
            }

            // Проверка строки
            if (typeof fieldValue === 'string') {
                return fieldValue.trim() === '';
            }

            // Проверка массива
            if (Array.isArray(fieldValue)) {
                if (fieldValue.length === 0) return true;

                // Проверка массива объектов с полем VALUE
                return fieldValue.some(item =>
                    item && typeof item.VALUE !== 'undefined' && item.VALUE.toString().trim() === ''
                );
            }

            // Проверка числа
            if (typeof fieldValue === 'number') {
                return isNaN(fieldValue);
            }

            // Проверка объекта (не массива)
            if (typeof fieldValue === 'object' && !Array.isArray(fieldValue)) {
                return Object.keys(fieldValue).length === 0;
            }

            return false;
        };

        return {
            taskStatus_m,
            canBeSaved_,
            getmomment,
            datataskCopy,
            getCopyTask,
            taskStatus_v,
            makeData,
            is_required_field,
            makedatataskCopy,
            projectOrder,
            projectTask,
            frequencyCyclicality,
            frequency
        };
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
        ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
        ...BX.Vue3.Pinia.mapState(cataloglistStore, ['data3','message']),
        ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
        ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
        ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),

        // Добавляем вычисляемое свойство для общей стоимости
        totalProjectCost() {
            let total = 0;

            for (let taskIndex in this.datataskCopy) {
                const task = this.datataskCopy[taskIndex];
                // Проверяем, что задача принадлежит текущему проекту
                if (task.UF_PROJECT_ID == this.project_id) {
                    total += parseFloat(task.FINALE_PRICE) || 0;
                }
            }

            const amount = Math.round(total * 100) / 100; // Округляем до 2 знаков

            // Получаем хранилище биллинга
            const billing = billingStore();

            // Устанавливаем недостающую сумму
            billing.setMissingAmount(amount);

            return amount;
        },

        project(){
            if (!PHPPARAMS.PROJECT_ID) return [];
            for (p of this.data){
                if (p.ID == this.project_id) return p;
            }
        }
    },
    methods: {
        ...helperVueComponents(),
        ...searchProduct(),
        //addbuttorder
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/task.js
        ...taskMethods(),
        ...addNewMethods(),
        ...BX.Vue3.Pinia.mapActions(calendarStore, ['updatecalendare','getEventsByTaskId']),
        ...BX.Vue3.Pinia.mapActions(brieflistStore, ['getRequireFields']),
        /**
         * Проверяет достаточно ли средств на балансе для запуска задачи
         * @param {number} taskIndex - индекс задачи
         * @returns {boolean} true если средств достаточно
         */
        checkBalance___(taskIndex) {
            const task = this.datataskCopy[taskIndex];
            const balance = this.databilling?.VALUE || 0;
            const taskPrice = task.FINALE_PRICE || 0;

            if (taskPrice > balance) {
                const kabinetStore = usekabinetStore();
                kabinetStore.Notify = '';
                kabinetStore.Notify = "У вас недостаточно средств!";

                // Устанавливаем недостающую сумму в billingStore
                const missingAmount = Math.ceil(taskPrice - balance);
                this.setMissingAmount(missingAmount);


                return false;
            }
            return true;
        },

        /**
         * Проверяет достаточно ли средств на балансе для запуска всех задач проекта
         * @returns {boolean} true если средств достаточно для всех задач
         */
        checkBalance(index) {
            const billing = billingStore();
            const balance = billing.databilling?.UF_VALUE || 0;
            let totalTaskPrice = 0;

            const current_task = this.datataskCopy[index];
            const TaskPrice = parseFloat(current_task.FINALE_PRICE) || 0;

            // Суммируем стоимость всех задач проекта
            for (let taskIndex in this.datataskCopy) {
                const task = this.datataskCopy[taskIndex];
                // Проверяем, что задача принадлежит текущему проекту
                if (task.UF_PROJECT_ID == this.project_id) {
                    totalTaskPrice += parseFloat(task.FINALE_PRICE) || 0;
                }
            }

            if (TaskPrice > balance) {
                const kabinetStore = usekabinetStore();
                kabinetStore.Notify = '';
                kabinetStore.Notify = "У вас недостаточно средств для запуска всех задач проекта!";

                // Устанавливаем недостающую сумму в billingStore
                const missingAmount = Math.ceil(totalTaskPrice - balance);
                this.setMissingAmount(missingAmount);

                return false;
            }
            return true;
        },

        // Новый метод для установки недостающей суммы
        setMissingAmount(amount) {
            // Получаем хранилище биллинга
            const billing = billingStore();

            // Устанавливаем недостающую сумму
            billing.setMissingAmount(amount);

            // Скроллим к компоненту пополнения баланса
            this.scrollToDepositComponent();
        },

// Метод для скролла к компоненту пополнения
        scrollToDepositComponent() {
            this.$nextTick(() => {
                const depositComponent = document.querySelector('.deposit-block-1');
                if (depositComponent) {
                    setTimeout(() => {
                        depositComponent.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 100);
                }
            });
        },

        starttask(index){
            // Проверяем баланс
            if (!this.checkBalance(index)) {
                return;
            }

            var cur = this;
            console.log(this.datataskCopy);
            var form_data = this.dataToFormData(this.datataskCopy[index]);
            this.saveData('bitrix:kabinet.evn.taskevents.starttaskcopy',form_data,function(data){

                for (index_ in data.task){
                    cur.datataskCopy[index_] = data.task[index_];
                }

                // Обновляем календарь
                const QueueStore = calendarStore()
                QueueStore.datacalendarQueue = data.queue;
                cur.updatecalendare([],cur.project_id);

                const taskStory = tasklistStore();
                taskStory.datatask = data.task;

                if (typeof data.data2 != "undefined") {
                    const orderStore = orderlistStore();
                    orderStore.data2 = data.data2;
                }

                // обновляем биллинг если он пришел от сервера
                if (typeof data.billing != "undefined") {
                    const billing = billingStore();
                    billing.databilling = data.billing;
                }


                cur.animatedCounter(taskStory.datatask[index].ID);

            });
        },
        starttask_(taskindex){
           this.runCommand(this.datataskCopy[taskindex],'start');
        },
        /*
        Остановить
        */
        stoptask(taskindex){

            const component = this.$refs.modalqueststop;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_1(taskindex){

            const component = this.$refs.modalqueststopcyclicality1;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_2_planned(taskindex){

            const component = this.$refs.modalqueststopcyclicality2planned;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_2_worked(taskindex){

            const component = this.$refs.modalqueststopcyclicality2worked;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_33_planned(taskindex){

            const component = this.$refs.modalqueststopcyclicality33planned;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_33_worked(taskindex){

            const component = this.$refs.modalqueststopcyclicality33worked;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_34_planned(taskindex){

            const component = this.$refs.modalqueststopcyclicality2planned;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        stoptask_cyclicality_34_worked(taskindex){

            const component = this.$refs.modalqueststopcyclicality2worked;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(this.datatask[taskindex],'stoptask');
            });
        },
        /*
        Удалить в архив
        */
        removetask(taskindex){
            const component = this.$refs.modalquestremove;

			// 14 - Остановлена	или только что создана
			if(this.datatask[taskindex].UF_STATUS != 14 && this.datatask[taskindex].UF_STATUS != '' && this.datatask[taskindex].UF_STATUS != 0){
				component.addAlert("Задачу сначала необходимо остановить!");
			}
            component.showmodale(taskindex,function(taskindex){                	
				this.runCommand(this.datatask[taskindex],'removetask');
				
            });
        },
        savetask:function(index){
            var cur = this;

            var form_data = this.dataToFormData(this.datatask[index]);

            form_data.delete("UF_DATE_COMPLETION");
            form_data.delete("UF_NUMBER_STARTS");
            form_data.delete("UF_DATE_COMPLETION_ORIGINAL");
            form_data.delete("UF_NUMBER_STARTS_ORIGINAL");
            form_data.delete("UF_CYCLICALITY");
            form_data.delete("UF_CYCLICALITY_ORIGINAL");


            this.saveData('bitrix:kabinet.evn.taskevents.edittask',form_data,function(data){
                // Обновляем календарь
                const QueueStore = calendarStore()
                QueueStore.datacalendarQueue = data.queue;
                cur.updatecalendare([],cur.project_id);

                const taskStory = tasklistStore();
                taskStory.datatask = data.task;

                cur.makeData(cur.datatask);

                //for (index in data.task) cur.datataskCopy[index] = data.task[index];
            });
        },
        savetaskCopy:function(index){
            var cur = this;
            var form_data = this.dataToFormData(this.datataskCopy[index]);
            this.saveData('bitrix:kabinet.evn.taskevents.edittaskcopy',form_data,function(data){

                for (item of data.task) for (fld in item) if (allowable.indexOf(fld) == -1) delete item[fld];
                for (index in data.task){
                    cur.datataskCopy[index] = data.task[index];
                }

                //const taskStory = tasklistStore();
                //taskStory.datatask = data.task;
            });
        },
        addmoreinput: function (task) {
            const kabinetStore = usekabinetStore();
            if (task.UF_TARGET_SITE.length > 4){
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Привышен лимит добавления";
                return;
            }
            task.UF_TARGET_SITE.push({ VALUE:'' });
		},
		removeimg: function(id_photo,taskindex){
            this.datatask[taskindex].UF_PHOTO_DELETE = id_photo;
			this.savetask(taskindex);
		},
        showpiclimits: function (pics,taskindex){
            let ret = [];
            if (typeof this.datatask[taskindex].LIMIT === 'undefined') this.datatask[taskindex].LIMIT = this.limitpics;
            pics.forEach((value,index) =>{
                if (index<this.datatask[taskindex].LIMIT) ret.push(value);
            });

            return ret;
        },
        inpsave: function (task_index){

            if (typeof this.$root.inpSaveTimer != 'undefined') clearTimeout(this.$root.inpSaveTimer);
            this.$root.inpSaveTimer = setTimeout(()=>{this.savetask(task_index);},5000);
        },

        // Инициализация калькулятора
        initCalculator() {
            this.taskCalculator = new TaskCalculator(this);
        },

        // Пересчет задачи при изменении параметров
        recalculateTask(taskIndex) {
            const task = this.datataskCopy[taskIndex];
            const product = this.getProductByIndexTask(taskIndex);

            if (!task || !product) return;

            const recalculatedTask = this.taskCalculator.recalculateTask(task, product);

            // Обновляем только расчетные поля
            this.datataskCopy[taskIndex] = {
                ...this.datataskCopy[taskIndex],
                UF_DATE_COMPLETION: recalculatedTask.UF_DATE_COMPLETION,
                FINALE_PRICE: recalculatedTask.FINALE_PRICE,
                RUN_DATE: recalculatedTask.RUN_DATE
            };
        },

        // Обновленный метод remakeTaskData
        remakeTaskData(taskIndex) {
            const task = this.datataskCopy[taskIndex];
            if (!task) return;

            // 1. Инициализация UF_DATE_COMPLETION_ORIGINAL (аналог первого цикла в remakeData)
            if (!task.UF_DATE_COMPLETION_ORIGINAL) {
                task.UF_DATE_COMPLETION_ORIGINAL = {};
            }

            task.UF_DATE_COMPLETION_ORIGINAL.MINDATE = task.UF_DATE_COMPLETION;
            task.UF_DATE_COMPLETION_ORIGINAL.MAXDATE = moment().add(1, 'year').unix();

            if (task.UF_DATE_COMPLETION_ORIGINAL.MINDATE > task.UF_DATE_COMPLETION_ORIGINAL.MAXDATE) {
                task.UF_DATE_COMPLETION_ORIGINAL.MAXDATE = task.UF_DATE_COMPLETION_ORIGINAL.MINDATE;
            }

            // 2. Получаем продукт
            const product = this.getProductByIndexTask(taskIndex);
            if (!product) return;

            // 3. Устанавливаем количество по умолчанию если пустое
            if (!task.UF_NUMBER_STARTS) {
                task.UF_NUMBER_STARTS = product.QUANTITY || 1;
            }

            // 4. FINALE_PRICE уже пересчитан в recalculateTask

            // 5. Обновляем QUEUE_STATIST (если нужно)
            // task.QUEUE_STATIST = this.getQueueStatistics(task);

            // 6. Фильтруем доступные варианты цикличности
            if (product.TASK_CONTINUITY && product.TASK_CONTINUITY.VALUE_XML_ID) {
                const possibleOptions = this.getPossibleCyclicalityOptions(product.TASK_CONTINUITY.VALUE_XML_ID);

                if (task.UF_CYCLICALITY_ORIGINAL && Array.isArray(task.UF_CYCLICALITY_ORIGINAL)) {
                    task.UF_CYCLICALITY_ORIGINAL = task.UF_CYCLICALITY_ORIGINAL.filter(option =>
                        possibleOptions.includes(option.ID)
                    );
                }
            }

            // 7. Форматируем значения цикличности (аналог второго цикла в remakeData)
            if (task.UF_CYCLICALITY_ORIGINAL && Array.isArray(task.UF_CYCLICALITY_ORIGINAL)) {
                for (const option of task.UF_CYCLICALITY_ORIGINAL) {
                    if (!option.VALUE) continue;

                    // 1 - Однократное выполнение
                    if (option.ID == 1) {
                        // Используем RUN_DATE из recalculateTask вместо calculateDateStart
                        option.VALUE = 'равномерно с ' + task.RUN_DATE + ' до заданной даты';
                    }

                    // 2 - Повторяется ежемесячно
                    if (option.ID == 2) {
                        // Используем RUN_DATE из recalculateTask вместо calculateDateStart
                        option.VALUE = 'ежемесячно, начиная с ' + task.RUN_DATE;
                    }
                }
            }

            // 8. RUN_DATE уже рассчитан в recalculateTask - ничего не делаем

            // Обновляем реактивные данные
            this.datataskCopy[taskIndex] = { ...task };
        },

        // Упрощенный getPossibleCyclicalityOptions
        getPossibleCyclicalityOptions(taskContinuityXmlId) {
            const TASK_CONTINUITY = {
                '5f08a50f317495840fe150a6556e3d43': [33],    // Одно исполнение
                '9295af06d671d06eb0bf036c3886f9d3': [1],     // Только однократные
                '4e6662937b21b89d5c02879b7e47718b': [2],     // Непрерывная задача
                '51e37ecf0978bf080600464552b95d1f': [1, 2],  // Однократная или непрерывная
                'fb226d4fc4447d5c81e2a902042ffca3': [34],    // Ежемесячная услуга
            };

            return TASK_CONTINUITY[taskContinuityXmlId] || [];
        },

        inpsaveCopyOLD(task_index){
            if (typeof this.$root.inpSaveTimer != 'undefined') clearTimeout(this.$root.inpSaveTimer);
            this.$root.inpSaveTimer = setTimeout(()=>{this.savetaskCopy(task_index);},2000);
        },

        // Обновленный метод inpsaveCopy
        inpsaveCopy(taskIndex) {
            // Сначала пересчитываем локально
            this.recalculateTask(taskIndex);

            // ДОБАВЛЕННАЯ ЛОГИКА: аналог remakeData для конкретной задачи
            // Используем данные из recalculateTask (UF_DATE_COMPLETION, RUN_DATE и т.д.)
            this.remakeTaskData(taskIndex);
        },

        getProductByIndexTask(index) {
            // 1. Достаём исходные данные задачи (без реактивной обёртки)
            const rawTask = BX.Vue3.toRaw(this.datatask[index]);

            // 2. Проверка наличия обязательных полей
            if (!rawTask?.UF_PRODUKT_ID || !rawTask?.UF_PROJECT_ID) {
                return null;
            }

            // 3. Поиск по исходному массиву проектов (не реактивному)
            const rawData = BX.Vue3.toRaw(this.data);
            const project = rawData.find(p => p.ID === rawTask.UF_PROJECT_ID);

            // 4. Доступ к исходному объекту заказов
            const rawOrder = BX.Vue3.toRaw(this.data2)[project?.UF_ORDER_ID];

            // 5. Возвращаем продукт (или null) без реактивной обёртки
            return rawOrder?.[rawTask.UF_PRODUKT_ID] ?? null;
        },
        runCommand:function(task,action){
            var cur = this;

            kabinet.loading();

            var form_data = new FormData();
            form_data.append('ID', task['ID']);

            const kabinetStore = usekabinetStore();
            const queryAction = 'bitrix:kabinet.evn.taskevents.'+action;
            BX.ajax.runAction(queryAction, {
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

                    // Обновляем календарь
                    const QueueStore = calendarStore()
                    QueueStore.datacalendarQueue = data.queue;
                    cur.updatecalendare([],cur.project_id);

                    const taskStory = tasklistStore();
                    taskStory.datatask = data.task;

                    if (typeof data.data2 != "undefined") {
                        const orderStore = orderlistStore();
                        orderStore.data2 = data.data2;
                    }

                    // обновляем биллинг если он пришел от сервера
                    if (typeof data.billing != "undefined") {
                        const billing = billingStore();
                        billing.databilling = data.billing;
                    }

                    //console.log(response)
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
                });

        },
        viewTask(TASK_ID){
            const block = document.querySelector('#produkt'+TASK_ID);
            if (block) document.querySelector('#produkt'+TASK_ID).scrollIntoView({behavior: 'smooth'});
        },
        showOne1(CYCLICALITY){
            for(index in CYCLICALITY){
                if(CYCLICALITY[index]['ID'] == 1) return CYCLICALITY[index]['VALUE'];
            }

            return '';
        },
        saveButton(taskindex){
            this.savetask(taskindex);
            this.datatask[taskindex].ID
            let node = document.querySelectorAll("#taskbutton2"+this.datatask[taskindex].ID);
            if (node.length>0) node[0].removeAttribute("disabled");

            node = document.querySelectorAll("#taskbutton1"+this.datatask[taskindex].ID);
            if (node.length>0) node[0].removeAttribute("disabled");
        },
        // убираем пустой элемент из селекта
        clearFirstItem(list){
            let new_list = [];
            for(index in list)
                if (index > 0) new_list.push(list[index]);

            return new_list;
        },
        dateStartNextMounth(){
            return moment().add(1, 'months').startOf('month');
        },
        dateEndNextMounth(){
            return moment().add(1, 'months').endOf('month');
        },
        animatedCounter(task_id){
            const c = this.taskStatus_v(task_id)['stopwark'];
             setTimeout(()=> {
                 if (this.anim_counter[task_id] < c) {
                     this.anim_counter[task_id] = this.anim_counter[task_id] + 1;
                     this.animatedCounter(task_id);
                 }
             },200);
        }
    },
    mounted() {
        // Функция для получения параметра из URL
        const getActionParameter = () => {
            // GET параметр
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'add_service') return true;

            // POST параметр
            if (typeof window.POST_PARAMS !== 'undefined' &&
                window.POST_PARAMS.action === 'add_service') {
                return true;
            }

            return false;
        };

        $('.external-events .fc-event').each(function() {
            $(this).data('event', {
                title: $.trim($(this).text()),
                stick: true,
                className: 'fc-event-' + $(this).attr('data-event')
            });

            $(this).draggable({
                zIndex: 999,
                revert: true,
                revertDuration: 0
            });
        });

        var node = BX("calendar1");
        $(node).fullCalendar({
            themeSystem: 'bootstrap4',
            locale: 'ru',
            height: 650,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: '',	//'yearview,quarter,month',
                ...parseJSON( node.getAttribute( 'data-fullcalendar-header' ) )
            },
            views: {
                quarter: {
                    type: 'timeline',
                    buttonText: 'Квартал',
                    dateIncrement: { years: 1 },
                    slotDuration: { months: 3 },
                    visibleRange: function (currentDate) {
                        return {
                            start: currentDate.clone().startOf('year'),
                            end: currentDate.clone().endOf("year")+1

                        };
                    }
                },
                yearview: {
                    type: 'timeline',
                    buttonText: 'Год',
                    dateIncrement: { years: 1 },
                    slotDuration: { months: 1 },
                    visibleRange: function (currentDate) {
                        return {
                            start: currentDate.clone().startOf('year'),
                            end: currentDate.clone().endOf("year")+1

                        };
                    }
                }
            },
            editable: false,
            droppable: false,
            drop: function() {
                // is the "remove after drop" checkbox checked?
                if (!$(this).hasClass('event-recurring')) {
                    $(this).remove();
                }
            },
            eventRender: function(event, element) {
                // кнопка закрыть на календаре
                //$(element).append( "<span class='event-close fa-times'></span>" );
                $(element).find('.event-close').click(function() {
                    $( node ).fullCalendar('removeEvents',event._id);
                });
            },
            weekNumbers: false,
            weekNumbersWithinDays : true,
            eventLimit: true
        });

        this.updatecalendare([],this.project_id);
        if (window.location.hash) document.querySelector(window.location.hash).scrollIntoView({behavior: 'smooth'});

        for(index in this.data3) {
            this.listprd.push(this.data3[index]);
        }

        /*
        var c;
        for(index in this.datatask) {
            c = this.taskStatus_v(index)['stopwark'];
            this.anim_counter[index] = c;
        }
         */


        this.anim_counter = this.datatask.reduce((acc, task) => {
            acc[task.ID] = this.taskStatus_v(task.ID).stopwark;
            return acc;
        }, {});

        this.initCalculator();

        // ДОБАВЛЕННЫЙ КОД: Автоматическое открытие модального окна при отсутствии задач
        const shouldShowModal = !this.datatask || this.datatask.length === 0 ||
            getActionParameter();

        if (shouldShowModal) {
            this.modaldata.project = this.project.ID;

            if (this.project.UF_ORDER_ID) {
                this.modaldata.order = this.project.UF_ORDER_ID;
            }

            // Используем setTimeout чтобы дать Vue полностью отрендерить компонент
            this.$nextTick(() => {
                setTimeout(() => {
                    this.myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
                    this.myModal.show();
                }, 100);
            });
        }

    },
	components: {
			myInputFileComponent,
            mydatepicker,
            questiona_ctivity_component,
            textInfoTask,
            timeLineTask
	},
        // language=Vue
    template: '#kabinet-content'
});

            taskApplication.config.globalProperties.PHPPARAMS = PHPPARAMS;
            configureVueApp(taskApplication);
        }
    }
}());