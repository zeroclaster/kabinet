const timeLineTask = BX.Vue3.BitrixVue.mutableComponent('time-Line-Task', {
    template: `
<div class="timelinetask-block">
    <div class="d-flex">
 
    <template  v-for="runner in getTaskQueueTimeLine(taskindex)">
    <div>
        <template v-if="runner">
        <div :class="'item '+alertStyle(runner.UF_STATUS)">
            <div></div>
            <div>{{runner.DATE}}</div>
        </div>
        </template>
        <template v-else>
        <div class="item">
            <div></div>
            <div></div>
        </div>
        </template>
    </div>
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
        getTaskQueueTimeLine(taskindex){
            let Queue = [];
            let ret = [];
            for(i in this.datacalendarQueue){
                if (this.datacalendarQueue[i].UF_TASK_ID == this.datatask[taskindex].ID){
                    Queue.push(this.datacalendarQueue[i]);
                }
            }

            if(!Queue.length) return [];

            var today = moment().endOf("month");


            for(i in Queue) {
               const UF_PLANNE_DATE = moment.unix(Queue[i].UF_PLANNE_DATE);
                ret.push({
                    ID:Queue[i].ID,
                    DATE:moment.unix(Queue[i].UF_PLANNE_DATE).format("DD.MMM"),
                    UF_PLANNE_DATE:Queue[i].UF_PLANNE_DATE,
                    UF_STATUS_ORIGINAL:Queue[i].UF_STATUS_ORIGINAL,
                    UF_STATUS:Queue[i].UF_STATUS
                });
               if (typeof Queue[parseInt(i)+1] != "undefined") {
                   let UF_PLANNE_DATE2 = moment.unix(Queue[parseInt(i)+1].UF_PLANNE_DATE)
                   let diff = UF_PLANNE_DATE2.diff(UF_PLANNE_DATE, 'days');
                   for (j=0;j<diff;j++){
                       ret.push(0);
                   }
               }
            }

            let ret2 = [];
            for(i=ret.length-1;i>-1;i--){
                if (ret[i]){
                    var endMonth = moment.unix(ret[i].UF_PLANNE_DATE).endOf("month");
                    let diff = endMonth.diff(moment.unix(ret[i].UF_PLANNE_DATE), 'days');
                    for (j=0;j<diff;j++){
                        ret2.push(0);
                    }
                    if (ret2.length>0) ret2[ret2.length-1]={
                        ID:0,
                        DATE:endMonth.format("DD.MMM"),
                        UF_PLANNE_DATE:0,
                        UF_STATUS_ORIGINAL: {CSS:''},
                        UF_STATUS:null
                    };
                    break;
                }
            }



            return [...ret, ...ret2];
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