var calendarTask_Application = document.calendarTask_Application || {};
calendarTask_Application = (function (){
    return {
        start(PHPPARAMS){

            const calendarTaskApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        counter: {
                            done: 0,
                            inprogress: 0,
                            planned: 0,
                        },
                        'project_id':PHPPARAMS.PROJECT_ID,
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
                },
                methods: {
                    updatecalendare:function(e){
                        let newData = [];
                        var cur = this;

                        // решающий статус
                        const dataStatus = [1,2,3,4,5,6,7,8];

                        let projectTask = [];
                        for(item of this.datatask)
                            if(item.UF_PROJECT_ID == this.project_id) projectTask.push(item.ID);


                        const $clk = new Map();
                        for(Queue of this.datacalendarQueue){

                            if (projectTask.indexOf(Queue.UF_TASK_ID) == -1) continue;

                            if (!$clk.has(Queue.UF_TASK_ID)) {
                                $clk.set(Queue.UF_TASK_ID, {
                                    'START': [],
                                    'STATUS':'',
                                    'STATUS2':'',
                                    'STATUS_ID':0
                                })
                            }

                            $clk.get(Queue.UF_TASK_ID).START.push(Queue['UF_PLANNE_DATE_ORIGINAL']['TIMESTAMP']);
                            $clk.get(Queue.UF_TASK_ID).STATUS = Queue.UF_STATUS_ORIGINAL.CSS;

                            /*
                            Смотрим, есть ли статусы которые считаем приорететными
                             */
                            if (dataStatus.indexOf(parseInt(Queue.UF_STATUS_ORIGINAL.VALUE)) !== -1)
                                        $clk.get(Queue.UF_TASK_ID).STATUS2 = Queue.UF_STATUS_ORIGINAL.CSS;

                            $clk.get(Queue.UF_TASK_ID).STATUS_ID = Queue.UF_STATUS_ORIGINAL.VALUE;
                        }
						
						
                        for (task of this.datatask){
                            if ($clk.has(task.ID)) {
                                let startDate = $clk.get(task.ID).START.sort();

                                let printStatus = $clk.get(task.ID).STATUS;
                                if ($clk.get(task.ID).STATUS2) printStatus = $clk.get(task.ID).STATUS2;

                                    newData.push({
                                    'title': task.UF_NAME+" (#id:"+task.ID+")",
                                    "start": moment(startDate[0], "X").format("YYYY-MM-DD"),
                                    "end": task.UF_DATE_COMPLETION_ORIGINAL.FORMAT2,
									//"start": task.UF_DATE_COMPLETION_ORIGINAL.FORMAT2,
                                    "className": printStatus,
                                    "url": '/kabinet/projects/planning/?p='+task.UF_PROJECT_ID+'#produkt'+task.UF_PRODUKT_ID
                                });
                            }
                        }

                        let inprogress_count = 0;
                        let planned_count = 0;
                        let done_count = 0;
                        let canceled_count = 0;
                        $clk.forEach(function (element) {			
                            console.log(element);
                            if (element.STATUS2) inprogress_count = inprogress_count + 1;
                            else{
                                if (element.STATUS_ID == 0) planned_count = planned_count + element.START.length;
                                if (element.STATUS_ID == 9) done_count = done_count + element.START.length;
                                if (element.STATUS_ID == 10) done_count = done_count + element.START.length;
                            }
                        });

                        //Функция позволяет изменить свойства узла node.
                        BX.adjust(BX('done_calendar_counter'), {text: done_count});
                        BX.adjust(BX('inprogress_calendar_counter'), {text: inprogress_count});
                        BX.adjust(BX('planned_calendar_counter'), {text: planned_count});
                        //пока не используется
                        //BX.adjust(BX('canceled_calendar_counter'), {text: canceled_count});

                        let fullCalendar = $("#calendar1");
                        fullCalendar.fullCalendar('removeEvents');
                        fullCalendar.fullCalendar('removeEventSources');
                        fullCalendar.fullCalendar( 'addEventSource', newData );

                    }
                },
                created(){
                },
                mounted() {
                    var cur = this;
                    cur.updatecalendare();
                },
                components: {
                },
                // language=Vue
                template: ''
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
            calendarTaskApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            calendarTaskApplication.config.globalProperties.$id = getId;

            calendarTaskApplication.use(store);
            calendarTaskApplication.mount('#calendar1vue');
        }
    }
}());