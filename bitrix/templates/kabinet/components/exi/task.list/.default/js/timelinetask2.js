const timeLineTask = BX.Vue3.BitrixVue.mutableComponent('time-Line-Task', {
    template: `
<div class="timelinetask-block">
    <div class="d-flex">
    <template v-for="runner in getTaskQueueTimeLine(taskindex)">
        <template v-if="runner">
        <div :class="'item '+runner.data1">
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
        getTaskQueueTimeLine(taskindex){
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
                let m_end = moment.unix(Queue[0].UF_PLANNE_DATE).add(i, 'days').endOf("month");
                let obj = null;
                for (j=0;j<Queue.length;j++) {
                    let d = moment.unix(Queue[j].UF_PLANNE_DATE);
                    if (day.isSame(m_end, 'day') && day.isSame(moment.unix(Queue[j].UF_PLANNE_DATE), 'day'))
                        obj = {
                            day:day.format("DD.MMM"),
                            data1:m_end.format("DD.MMM"),
                            css:this.alertStyle(Queue[j].UF_STATUS)
                        };
                    else if (day.isSame(moment.unix(Queue[j].UF_PLANNE_DATE), 'day'))
                        obj = {
                            day:day.format("DD.MMM"),
                            data1:"",
                            css:this.alertStyle(Queue[j].UF_STATUS)
                        };
                }

                if(!obj && day.isSame(m_end, 'day'))
                    obj = {day:day.format("DD.MMM"), data1:m_end.format("DD.MMM"), css:""};
                else if(!obj)
                    obj = {day:day.format("DD.MMM"), data1:"", css: ""};

                ret.push(obj);
            }

            return ret;
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