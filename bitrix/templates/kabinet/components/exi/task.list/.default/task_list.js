var task_list = document.task_list || {};
task_list = (function (){
    return {
        start(PHPPARAMS){

            if (typeof PHPPARAMS.PROJECT_ID === "undefined" || PHPPARAMS.PROJECT_ID == '')
                throw "Field PROJECT_ID not found!";


// https://getdatepicker.com/4/
// Set datepicker's value to initial date
//const newDate = moment(this.original, "DD.MM.YYYY");
//$(this.$refs.input).data('DateTimePicker').date(newDate);

//https://momentjs.com/docs/#/displaying/
/*
moment().day(-7); // last Sunday (0 - 7)
moment().day(0); // this Sunday (0)
moment().day(7); // next Sunday (0 + 7)
moment().day(10); // next Wednesday (3 + 7)
moment().day(24); // 3 Wednesdays from now (3 + 7 + 7 + 7)
 */
const mydatepicker = BX.Vue3.BitrixVue.mutableComponent('date-picker', {
    template: `
<input ref="input" :id="id_input" :data-mind="mindd" type="text" @change="sendvalinput" v-model="localModelValue" class="form-control"/>
<div class="input-group-append">
    <label class="input-group-text" :for="id_input"><span class="fa fa-calendar"></span></label>
</div>
`,	data(){
        return{
            datechenge: {},
			id_input:'#inpid'+kabinet.uniqueId()
        }
    },
    props: [
        'modelValue',   // значение даты из базы
        'tindex',
        'mindd',        // минимальная дата
        'maxd',         // максимальная дата
        'original'      // значение из базы в формате FORMAT1
    ],
    computed: {
        localModelValue: {
            /* liveHack
             нужна что бы можно было обновлять modelValue, и не возникала ошибка modelValue только для чтения
             тут v-model приходит в переменной props: ['modelValue'
            <mytypeahead v-model="runner.UF_LINK" ....
            и ее же помещаем в
            <input v-model="modelValue" ....
            для обновления сохраненного значения, но при изменении идет обращение к modelValue, но она только для чтения
             */
            get() {
                if (typeof this.modelValue != "undefined" &&  this.modelValue != "") {
                    return moment.unix(this.modelValue).format("DD.MM.YYYY");
                }else
                    return '';
            },
            set(newValue) {
                const newDate = moment(newValue, "DD.MM.YYYY");
                this.$emit('update:modelValue', parseInt(newDate.unix())+10800)
            },
        },
    },
	watch:{
        mindd: {
            handler(mind, oldVal) {
					if (mind) {
                                this.datechenge.options({
                                    minDate: moment.unix(mind).toDate(),     //new Date()
                                    maxDate: moment.unix(this.maxd).toDate(),
                                    disabledDates: [moment.unix(mind)]
                                });
					}
            },
            deep: true
        },
    },
    mounted () {
        // Add event handler

		let inp = $(this.$refs.input);
		let mind = $(this.$refs.input).data("mind");
        let mindate = false;
        let dateDEsable = false;

		if (mind) {
            let mindateObj = moment.unix(mind);
            // Если в календаре выставиться из базы минимальная дата, то переключившись на другую дату, потом нельзя выбрать обратно
            //минимальну дату, но она висит активной, добавляем ее в исключения dateDEsable
            dateDEsable = mindateObj;
            mindate = mindateObj.toDate();
        }

        //console.log([mindate,this.$root.datatask[this.tindex]]);

        let maxdateObj = moment.unix(this.maxd);
        let maxdate = maxdateObj.toDate();

        $(this.$refs.input).datetimepicker({
            locale: moment.locale('ru'),
            format: 'DD.MM.YYYY',
           // minDate: mindate,     //new Date()
            maxDate: maxdate
        })
            .on('dp.show',(event) => {
                //debugger
            })
            .on('dp.change', (event) => {

				const originalDate = moment(this.original, "DD.MM.YYYY");

				if (originalDate.format("X") != event.date.format("X"))
						this.updateValue(event.date);
            });

        $(this.$refs.input).data('DateTimePicker').options({
            minDate: mindate,
            disabledDates: [dateDEsable]
        });
        this.datechenge = $(this.$refs.input).data('DateTimePicker');

    },
    methods: {
        updateValue (value) {
            this.$emit('update:modelValue', parseInt(value.format('X'))+10800);
            // отключаем изменение даты на каждую выбранную дату
            //this.$root.savetask(this.tindex);
        },
        sendvalinput(event){
            const inp = event.target;
            if(moment(inp.value, "DD.MM.YYYY",true).isValid()){
                //console.log(['update input']);

                // отключаем изменение даты на каждую выбранную дату
				//this.$root.savetask(this.tindex);
            }
        }
    }

});


const myInputFileComponent = BX.Vue3.BitrixVue.mutableComponent('myInputFileComponent', {
	data(){
	    return{
            previmg:[],
            addtext:"Добавить фото"
        }
    },
    props: ['tindex'],
    computed: {
	    showtext(){
            if (this.previmg.length==0){
                return "Добавить фото";
            }else
                return "Заменить фото";
        },
        statsize(){
            var size = 0;
            for(UF_PHOTO_ORIGINAL of this.$root.datatask[this.tindex].UF_PHOTO_ORIGINAL){
                    size = size + parseInt(UF_PHOTO_ORIGINAL.FILE_SIZE);
            }

            return this.bytesToSize(size,1);
        },
        statcount(){
	        return "Всего: "+this.$root.datatask[this.tindex]['UF_PHOTO_ORIGINAL'].length;
        }
    },
    methods: {
	    bytesToSize(bytes, precision = 2) {
            if (bytes === 0) return '0 Байт';
            const units = ['Байт', 'КБ', 'МБ', 'ГБ', 'ТБ'];
            const index = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, index)).toFixed(precision) + ' ' + units[index];
        },
		onChangeFile(event) {
            console.log(event.target.files);
            var cur = this;
            const kabinetStore = usekabinetStore();

            this.previmg = [];

		    for (let file of event.target.files){
                if ((typeof file.type !== "undefined" ? file.type.match('image.*') : file.name.match('\\.(gif|png|jpe?g)$')) && typeof FileReader !== "undefined") {
                    /*
					var reader = new FileReader();               
                    reader.onload = function(e) {
                        cur.previmg.push({src:e.target.result,name:file.name});
                    }

                    reader.readAsDataURL(file)
					*/
                }else{
                    kabinetStore.Notify = "Error file type";
                    event.target.value = '';
                    return false;
                }
            }

		  this.$emit('update:modelValue', event.target.files);
		  this.$root.savetask(this.tindex);
		
		},
        removeimg(index){
            console.log(index);
            console.log(this.previmg);
            var cur = this;
            var newFileList = Array.from(this.$root.datatask[this.tindex].UF_PHOTO);
            var findindex = null;
            newFileList.forEach(function (file,index) {
                if (file.name = cur.previmg.name){
                    findindex = index;
                }
            });
            newFileList.splice(findindex,1);
            console.log(newFileList);
            this.$root.datatask[this.tindex].UF_PHOTO = newFileList;

            this.previmg.splice(index, 1);
            console.log(this.$root.datatask[this.tindex].UF_PHOTO);

        },
        savetask:function (tindex) {
            this.$root.savetask(tindex);
        }
	},
	template:`<div class="preview-img-block-1 addbutton d-flex justify-content-center align-items-center">
<div class="text-center">
<span class="add-images-marker-1"><i class="fa fa-cloud-download" aria-hidden="true"></i></span>
<div style="position: absolute;bottom: 0;left: 27%;font-size: 12px;">{{statcount}}</div>
<!--
<div>({{statsize}})</div>
-->
</div>
<input type="file" @change="onChangeFile" name="file"  multiple/>
</div>
`
});

// TODO убрать тестовые методы
const taskApplication = BX.Vue3.BitrixVue.createApp({
    data() {
        return {
            limitpics:5,
            project_id: PHPPARAMS.PROJECT_ID,
            modaldata: {title:'Добавить услугу',order:0,project:0},
            modal2data: {title:'Удалить услугу',message:'',question:'Вы действительно хотите удалить?',basketitem:0,order_id:0},
            myModal:{},
            myModal2:{},
            listprd: []
        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
        ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
        ...BX.Vue3.Pinia.mapState(cataloglistStore, ['data3','message']),
        ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
        ...BX.Vue3.Pinia.mapState(calendarStore, ['datacalendarQueue']),
        ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
        fullName: {
            // геттер:
            get: function () {
                console.log(this.a);
            },
            // сеттер:
            set: function (newValue) {
                console.log(newValue);
            }
        },
        project(){
            if (!PHPPARAMS.PROJECT_ID) return [];
            for (p of this.data){
                if (p.ID == this.project_id) return p;
            }
        }
    },
    methods: {
        ...searchProduct(),
        //addbuttorder
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/task.js
        ...taskMethods(),
        ...addNewMethods(),
        ...BX.Vue3.Pinia.mapActions(calendarStore, ['updatecalendare']),
        /*
        Остановить
        */
        starttask(taskindex){

           this.runCommand(taskindex,'start');

        },
        stoptask(taskindex){

            const component = this.$refs.modalqueststop;
            component.showmodale(taskindex,function(taskindex){
                this.runCommand(taskindex,'stoptask');
            });
        },
        /*
        Удалить в архив
        */
        removetask(taskindex){
            const component = this.$refs.modalquestremove;

			// 14 - Остановлена	или только что создана
			if(this.datatask[taskindex].UF_STATUS != 14 && this.datatask[taskindex].UF_STATUS != '' && this.datatask[taskindex].UF_STATUS != 0){
				component.addAlert("Задачу сначало необходимо остановить!");
			}
            component.showmodale(taskindex,function(taskindex){                	
				this.runCommand(taskindex,'removetask');
				
            });
        },
        projectOrder:function (id){
            //console.log(id);
            var findOrder = 0;
            this.data.forEach(function(element){
                if (!findOrder && element.ID == id){
                    findOrder = element.UF_ORDER_ID;
                }
            });

            return findOrder;
        },
        projectTask(project_id){
            let task = [];
            for(index in  this.datatask){
                if (this.datatask[index]['UF_PROJECT_ID'] == project_id) task.push(this.datatask[index]);
            }

            return task;
        },
        savetask:function(index){
            var cur = this;

            var form_data = this.dataToFormData(this.datatask[index]);

            this.saveData('bitrix:kabinet.evn.taskevents.edittask',form_data,function(data){
                // Обновляем календарь
                const QueueStore = calendarStore()
                QueueStore.datacalendarQueue = data.queue;
                cur.updatecalendare([],cur.project_id);

                const taskStory = tasklistStore();
                taskStory.datatask = data.task;
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
        showall: function (task) {
            task.LIMIT = 1000;
        },
        inpsave: function (task_index){

            if (typeof this.$root.inpSaveTimer != 'undefined') clearTimeout(this.$root.inpSaveTimer);
            this.$root.inpSaveTimer = setTimeout(()=>{this.savetask(task_index);},5000);
        },
        test: function (task){
            console.log(task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1);
            task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1 = '11.07.2024';
        },
		getProductByIndexTask: function(index){
			const task = this.datatask[index];			
			const UF_PRODUKT_ID = task['UF_PRODUKT_ID'];
			const UF_PROJECT_ID = task['UF_PROJECT_ID'];
			var product = [];
			var order = [];
			var orderID = 0;
				
			for(let breif of this.data){
				if (breif['ID'] == UF_PROJECT_ID){					
					orderID = breif['UF_ORDER_ID'];
					order = this.data2[orderID];
					product = order[UF_PRODUKT_ID];
					break;
				}
			}

			return product;	
		},
		frequency: function (index){
			var interval = 0;
			const product = this.getProductByIndexTask(index);
			if (product) {
				interval = product['MINIMUM_INTERVAL']['VALUE'];
			}
				
			let ret = '';			
			$interval_ = kabinet.timeConvert(interval,'days');
			$ret = $interval_+' дня.'
			if ($interval_ < 1) {
				$interval_ = kabinet.timeConvert(interval,'hours');
				$ret = $interval_+' часов.'
			}
					
			return $ret;
		},
        runCommand:function(index,action){
            var cur = this;
            var task;

            kabinet.loading();

            task = this.datatask[index];

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
                    //console.log(response);
                    kabinet.loading(false);
                    response.errors.forEach((error) => {
                        kabinetStore.Notify = '';
                        kabinetStore.Notify = error.message;
                    });

                });

        },
        countQueu(index){
            const task = this.datatask[index];
            var countTaskQueue = 0;
            for(queue of this.datacalendarQueue){
                if (queue.UF_TASK_ID == task.ID) countTaskQueue++;
            }
            return countTaskQueue;
        },
        viewTaskAlert(task_id){
            const task_alert = PHPPARAMS.TASK_ALERT;
            for(id in task_alert){
                if (task_id == id) return task_alert[id];
            }

            return '';
        },
        taskStatus(index){
            if (this.countQueu(index) == 0) return '<div class="status-task-1 text-warning">Не выполняется</div>';
            const task = this.datatask[index];

            let isRuned = 0;

            isRuned = 0;
            for(queue of this.datacalendarQueue){
                if (
                    queue.UF_TASK_ID == task.ID &&
                    (
                        queue.UF_STATUS == 10 ||
                        queue.UF_STATUS == 9
                    )
                ) {
                    isRuned++;
                }
            }

            if (isRuned == this.countQueu(index)) return '<div class="status-task-1 text-secondary">Остановлена</div>';

            isRuned = 0;
            for(queue of this.datacalendarQueue){
                if (
                    queue.UF_TASK_ID == task.ID &&
                    (
                        queue.UF_STATUS == 0
                    )
                ) {
                    isRuned++;
                }
            }

            if (isRuned > 0) return '<div class="status-task-1 text-warning">Запланирована</div><div class="ml-4">Завершится: '+task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1+'</div>';


            return '<div class="status-task-1 text-success">Выполняется</div><div class="ml-4">Завершится: '+task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1+'</div>';
        },
        viewTask(PRODUKT_ID){
            const block = document.querySelector('#produkt'+PRODUKT_ID);
            if (block) document.querySelector('#produkt'+PRODUKT_ID).scrollIntoView({behavior: 'smooth'});
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
        canBeSaved(taskindex){

            //debugger
            if (this.datatask[taskindex].ID > 0) var a = 1;

            const regex = new RegExp('_ORIGINAL');

            if (this.$root.defaultdatatask.length > 0)
            for (key in this.datatask){
                for (field in this.datatask[key]){

                    if (regex.test(field)) continue;

                    if (typeof this.datatask[key][field] == 'string') {
                        if (this.datatask[key][field] != this.$root.defaultdatatask[key][field])
                            return false;
                        /*
                            console.log([
                            this.datatask[key][field],
                            this.$root.defaultdatatask[key][field]
                        ]);
                        */
                    }
                    if (typeof this.$root.defaultdatatask[key][field] == 'object' && this.$root.defaultdatatask[key][field].length>0) {
                        for (k in this.$root.defaultdatatask[key][field]){
                            if (this.$root.defaultdatatask[key][field][k].VALUE) {
                                if (this.datatask[key][field][k].VALUE != this.$root.defaultdatatask[key][field][k].VALUE)
                                    return false;
                                /*
                                    console.log([
                                    field,
                                    this.datatask[key][field][k].VALUE,
                                    this.$root.defaultdatatask[key][field][k].VALUE
                                ]);
                                 */
                            }
                        }

                    }
                }
            }
            return true;
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
        }
    },
    created(){
        this.$root.defaultdatatask = [];
    },
    mounted() {
        this.updatecalendare([],this.project_id);
        this.$root.defaultdatatask = JSON.parse(JSON.stringify(this.datatask));
        if (window.location.hash) document.querySelector(window.location.hash).scrollIntoView({behavior: 'smooth'});

        for(index in this.data3) {
            this.listprd.push(this.data3[index]);
        }
    },
	components: {
			myInputFileComponent,
            mydatepicker,
            questiona_ctivity_component
	},
        // language=Vue
    template: '#kabinet-content'
});
taskApplication.use(store);

taskApplication.mount('#kabinetcontent');

        }
    }
}());