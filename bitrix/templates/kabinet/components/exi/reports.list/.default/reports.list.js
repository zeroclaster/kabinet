const reportsListApplicationConfig = {
data() {
    return {
        task_id:PHPPARAMS['TASK_ID'],
        countview:PHPPARAMS['viewcount'],
        total: PHPPARAMS['total'],
        showloadmore:true,
        limitpics:5,
        messageStoreInstance: null
    }
},
setup(){
    const {projectOrder, projectTask} = data_helper();

    return {
        projectOrder,
        projectTask,
        hiddenCommentBlock
    };
},
computed: {
    ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
    ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
    ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner']),
    TaskByIdKey(){
        let ret = {};
        for (index in this.datatask){
            ret[this.datatask[index].ID] = this.datatask[index];
        }

        return ret;
    },
    PRODUCT(){
        const order_id = this.projectOrder(this.TaskByIdKey[this.task_id].UF_PROJECT_ID);
        const product_id = this.TaskByIdKey[this.task_id].UF_PRODUKT_ID;
        let PRODUCT = this.data2[order_id][product_id];
        return PRODUCT;
    }
},
methods: {
    moreload:function (e) {
        const this_ = this;
        let formData = new FormData;
        this.$root.offset = this.$root.offset + 25;
        formData.append("OFFSET",this.$root.offset);
        for (fieldname in filterclientlist) formData.append(fieldname,filterclientlist[fieldname]);

        formData.append("countview",this_.countview);
        const kabinetStore = usekabinetStore();
        kabinet.loading();
        var data = BX.ajax.runComponentAction("exi:reports.list", "loadmore", {
            mode: 'class',
            data: formData,
            signedParameters: signedParameters,
            timeout: 300
        }).then(function (response) {
            kabinet.loading(false);
            const data = response.data;

            /*
            if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0){
                const message_store = messageStore();
                for(index in data.MESSAGE_DATA)
                    message_store.datamessage[index] = data.MESSAGE_DATA[index];
            }*/

            // ИСПРАВЛЕННЫЙ БЛОК - используем переданное хранилище
            if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0 && window.messageStoreInstance){
                for(index in data.MESSAGE_DATA) {
                    window.messageStoreInstance.datamessage[index] = data.MESSAGE_DATA[index];
                }
            }

            if (typeof data.RUNNER_DATA != "undefined" && data.RUNNER_DATA.length == 0) this_.showloadmore = false;
            if (this_.datarunner.length == this_.total) this_.showloadmore = false;
            //if (Object.keys(data.RUNNER_DATA).length == this_.total) this_.showloadmore = false;

            // исполнения
            if (typeof data.RUNNER_DATA != "undefined"){
                data.RUNNER_DATA.forEach((elm)=>{this_.datarunner.push(elm)});
            }

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
    showCommentWrite(index){
        const components = this.$refs.modaleCommnetWrite;
        for(i in components){
            if(components[i].tindex == index) components[i].showmodale();
        }
    },
    savetask: function (index){
        var cur = this;
        var runner;

        kabinet.loading();

        runner = this.datarunner[index];

        var form_data = new FormData();
        for ( var key in runner ) {
            console.log([key,runner[key]]);

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

                if (data.message == 'EmptyUF_COMMENT'){
                    setTimeout(() =>cur.showCommentWrite(index),1000);
                }else {
                    kabinetStore.NotifyOk = '';
                    kabinetStore.NotifyOk = data.message;
                }

                if (Object.keys(data.runner).length > 0) cur.datarunner[index] = data.runner;

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
    isShowReportLink(index){
        if (
            this.datarunner[index].UF_REPORT_TEXT_ORIGINAL ||
            this.datarunner[index].UF_REPORT_LINK_ORIGINAL ||
            this.datarunner[index].UF_REPORT_SCREEN_ORIGINAL ||
            this.datarunner[index].UF_REPORT_FILE_ORIGINAL
        ) return true;

        return false;
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
    },
    isUserrEdit(index){
        var runner;
        runner = this.datarunner[index];
        if (!runner) return true;
        // ненайдено
        if (["0","1","2","3","4","5"].indexOf(runner.UF_STATUS) == -1) return false;

        return true;
    },
    isViewSoglasovat(){
        const task = this.TaskByIdKey[this.task_id];
        // 5 - На согласовании (у клиента)
        // 8 - Отчет на проверке у клиента
        // bitrix/modules/kabinet/lib/task/taskmanagercache.php
        // public function getQueueStatistics(int $id)
        if (task.QUEUE_STATIST[4].COUNT > 0) return true;

        return false;
    },
    openImageModal(imageSrc) {
        // Устанавливаем src изображения в модальном окне
        document.getElementById('modalImage').src = imageSrc;

        // Показываем модальное окно (используем jQuery для Bootstrap)
        $('#imageModal').modal('show');
    }
},
created(){
},
// после отрисовки всех представлений
mounted() {
    var cur = this;
    this.$root.offset = 0;

    if(parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;

    if (Object.keys(filterclientlist).length > 1) {
        const el = document.querySelector(".report-list-block");
        if (el) el.scrollIntoView({behavior: 'smooth'});
    }
},
components: {
    mydatepicker,
    mytypeahead,
    sharephoto,
    messangerperformances,
    changestatus,
    commentWrite,
    shownote,
    richtext,
},
// language=Vue
template: '#kabinet-content'
};
