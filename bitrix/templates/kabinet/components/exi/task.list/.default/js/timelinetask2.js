const timeLineTask = BX.Vue3.BitrixVue.mutableComponent('time-Line-Task', {
    template: `
<div class="timelinetask-block">
    <div class="d-flex">
    <template v-for="runner in getTaskQueueTimeLine(taskindex)">
        <template v-if="runner">
        <div :class="'item '+runner.css">
            <div>{{runner.data1}}</div>
        </div>
        </template>
        <template v-else>
        <div class="item"></div>
        </template>
    </template>
    </div>
</div>
`,
    data(){
        return{
        }
    },
    props: ['taskindex'],
    computed: {
        ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
        ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
    },
    mounted () {
    },
    methods: {
        taskQueue(taskindex){
            let Queue = [];
            for(i in this.datacalendarQueue){
                if (this.datacalendarQueue[i].UF_TASK_ID == this.datatask[taskindex].ID){
                    Queue.push(this.datacalendarQueue[i]);
                }
            }

            return Queue;
        },
        getTaskQueueTimeLine__(taskindex){
            let Queue = [];
            let ret = [];

            Queue = this.taskQueue(taskindex);
            if(!Queue.length) return [];

            const first = moment.unix(Queue[0].UF_PLANNE_DATE);
            const last = moment.unix(Queue[Queue.length-1].UF_PLANNE_DATE);
            const endMonth = last.add(2, 'months').endOf("month");
            endMonth.add(1,"days")
            let diff = endMonth.diff(first, 'days');

            for (i=0;i<diff;i++) {
                let day = moment.unix(Queue[0].UF_PLANNE_DATE).add(i, 'days');
                let m_start = moment.unix(Queue[0].UF_PLANNE_DATE).add(i, 'days').startOf("month");
                let m_end = moment.unix(Queue[0].UF_PLANNE_DATE).add(i, 'days').endOf("month");
                let obj = null;
                for (j=0;j<Queue.length;j++) {
                    let d = moment.unix(Queue[j].UF_PLANNE_DATE);
                    if (day.isSame(m_start, 'day') && day.isSame(moment.unix(Queue[j].UF_PLANNE_DATE), 'day'))
                        obj = {
                            day:day.format("DD.MMM"),
                            data1:m_start.format("MMM.YY"),
                            css:this.alertStyle(Queue[j].UF_STATUS)
                        };
                    else if (day.isSame(moment.unix(Queue[j].UF_PLANNE_DATE), 'day'))
                        obj = {
                            day:day.format("DD.MMM"),
                            data1:"",
                            css:this.alertStyle(Queue[j].UF_STATUS)
                        };
                }

                if(!obj && day.isSame(m_start, 'day'))
                    obj = {day:day.format("DD.MMM"), data1:m_start.format("MMM.YY"), css:""};
                else if(!obj)
                    obj = {day:day.format("DD.MMM"), data1:"", css: ""};

                ret.push(obj);
            }

            console.log(ret);

            return ret;
        },
        getTaskQueueTimeLine(taskIndex) {
            const queue = this.taskQueue(taskIndex);
            if (!queue.length) return [];

            const firstDate = moment.unix(queue[0].UF_PLANNE_DATE);
            const lastDate = moment.unix(queue[queue.length - 1].UF_PLANNE_DATE);
            const endMonth = lastDate.clone().add(2, 'months').endOf('month').add(1, 'day');
            const dayCount = endMonth.diff(firstDate, 'days');

            // Создаем массив нужной длины и заполняем его по индексу
            const timeline = Array.from({ length: dayCount }, (_, i) => {
                const currentDay = firstDate.clone().add(i, 'days');
                const isFirstDayOfMonth = currentDay.isSame(currentDay.clone().startOf('month'), 'day');

                const taskForDay = queue.find(task =>
                    currentDay.isSame(moment.unix(task.UF_PLANNE_DATE), 'day')
                );

                return {
                    day: currentDay.format('DD.MMM'),
                    data1: isFirstDayOfMonth ? currentDay.format('MMM.YY') : '',
                    css: taskForDay ? this.alertStyle(taskForDay.UF_STATUS) : ''
                };
            });

            return timeline;
        },
        alertStyle(status){

            //Серая – запланировано.
            if ([0].indexOf(parseInt(status)) != -1) return 'alert-planned';

            //все стадии, когда ведутся работы на стороне сервиса
            if ([1,2,4,6,7].indexOf(parseInt(status)) != -1) return 'alert-worked';

            //все стадии, когда требуется внимание клиента – желтые+серый текст.
            if ([3,5,8].indexOf(parseInt(status)) != -1) return 'alert-user-attention';

            //Красные – отмена..
            if ([9].indexOf(parseInt(status)) != -1) return 'alert-cancel';

            //темно-серые – выполнено
            if ([10].indexOf(parseInt(status)) != -1) return 'alert-done';
        }
    }
});