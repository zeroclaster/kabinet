var reports_list = document.reports_list || {};
reports_list = (function (){
    return {
        start(PHPPARAMS,signedParameters){

            var filterclientlist = PHPPARAMS.FILTER;

            const commentWrite = BX.Vue3.BitrixVue.mutableComponent('comment Write', {
                template: `
<div class="modal fade" :id="ModalID" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title fs-5" id="exampleModalLabel">Введите комментарий</h3>
        </div>
        <div class="modal-body messanger-block">
                        <div class="sender-block">                
                        <div class="d-flex">
							<!--
                            <div class="upload-file-block">
                                    <messUploadFileComponent v-model="sendFiles"/>
                            </div>
							-->
                            <div class="message-text-block">
                                 <div class="upload-file-list d-flex flex-wrap" v-if="sendFiles.length>0">
                                        <div class="mr-2 p-2" v-for="(upl_file,fileIndex) of sendFiles">{{upl_file.name}} <div class="remove-upload-file text-primary" @click="removeUplFile(fileIndex)"><i class="fa fa-times" aria-hidden="true"></i></div></div>
                                </div>
                                    <textarea ref="textares" class="form-control" :name="$id('comment')" rows="10" v-model="localModelValue" placeholder="Напишите причину по которой вы отклоняете"></textarea>
                            </div>
                            <div class="sender-block ml-auto d-flex align-items-center">
                                    <button type="button" class="btn btn-primary btn-sm send-message-button" @click="save">Отправить</button>
                            </div>
                        </div>
                        </div>
        </div>
        <div class="modal-footer">
            
            <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
        </div>
    </div>
</div>
</div>
                `,
                data(){
                    return{
                        ModalID:'modale'+kabinet.uniqueId(),
                    }
                },
                props: ['modelValue','tindex'],
                computed: {
                    sendFiles:{
                        get() {
                            if (typeof this.$root.datarunner[this.tindex].messagePics != "undefined") return this.$root.datarunner[this.tindex].messagePics;
                            return [] },
                        set(newValue) {
                            this.$root.datarunner[this.tindex].messagePics = newValue;
                        },
                    },
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
                            return this.modelValue },
                        set(newValue) {
                            this.$emit('update:modelValue', newValue)
                        },
                    },
                },
                mounted () {
                    // Add event handler
                    const this_ = this;
                },
                methods: {
                    removeUplFile(fileIndex){
                        const dt = new DataTransfer();
                        let arr_files = [...this.sendFiles];
                        arr_files.forEach((f, i)=>{
                            if (i != fileIndex)
                                dt.items.add(f);
                        });

                        this.sendFiles = dt.files;
                    },
                    save:function () {
                        this.$.notrest = false;
                        this.closemodal();
                        this.$root.savetask(this.tindex);
                        this.$.notrest = true;
                    },
                    closemodal:function(){
                        this.$.myModal.hide();
                        this.$.ckEditor.destroy().catch( error => {
                                console.log( error );
                        } );
                    },
                    showmodale(){
                        const this_ = this;
                        if (typeof this.$.myModal == 'undefined') {
                            this.$.myModal = new bootstrap.Modal(document.getElementById(this.ModalID), {});
                            this.$.notrest = true;
                            $('#'+this.ModalID).on('hidden.bs.modal', function (e) {
                                if(this_.$.notrest)
                                    this_.$root.resetSave(this_.tindex)
                            })
                        }
                        this.$.myModal.show();


                        let node = this.$refs.textares;

                        CKEDITOR.ClassicEditor.create( node, this.ckSetup() ).then(  ( editor )=> {
                            this.$.ckEditor = editor;
                            editor.model.document.on('change:data', (evt, data) => {
                                //console.log(editor.getData());
                                this.$emit('update:modelValue', editor.getData());
                            });
                        });
                    },
                    ckSetup(){
                        return {
                            //toolbar: [ "heading", 'bold', 'italic', 'link',"imageUpload"],
                            toolbar: [  ],
                            minHeight: '600px',
                            heading: {
                                options: [
                                    { model: 'paragraph', title: 'Заголовок', class: 'ck-heading_paragraph' },
                                    { model: 'heading1', view: 'h2', title: 'Заголовок2', class: 'ck-heading_heading2' },
                                    { model: 'heading2', view: 'h3', title: 'Заголовок3', class: 'ck-heading_heading3' },
                                    { model: 'heading3', view: 'h4', title: 'Заголовок4', class: 'ck-heading_heading4' }

                                ]
                            },
                            ckfinder: {
                                // eslint-disable-next-line max-len
                                uploadUrl: '/tools/connector.php?command=QuickUpload&type=Files&responseType=json'
                            },
                            link: {
                                addTargetToExternalLinks: true
                            }
                        }
                    }
                },
                components: {
                    messUploadFileComponent,
                }
            });

            const changestatus = BX.Vue3.BitrixVue.mutableComponent('change status', {
                template: `
                          <div class="mt-3 statusworkblock" v-if="catalog.length>0">
                    <div class="h4">Действия:</div>
                    <div class="form-group select-status" v-for="Status in catalog">
                        <div class="form-check">
                          <input @change="saveStatus" :name="$id('name')" class="form-check-input" :id="$id(Status.ID)" v-model="localModelValue" type="radio" :value="Status.ID">
                          <label style="color: #FFF !important;" class="form-check-label text-primary btn btn-primary" :for="$id(Status.ID)" v-html="Status.USER_BUTTON"></label>
                        </div>
                    </div>
                </div>
                `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId()
                    }
                },
                props: ['modelValue','tindex','catalog'],
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
                        get() { return this.modelValue },
                        set(newValue) {
                            this.$emit('update:modelValue', newValue)
                        },
                    },
                },
                mounted () {
                    // Add event handler
                    const this_ = this;

                    this.$.iid = function() {
                        return "uid-" + kabinet.uniqueId();
                    };
                },
                methods: {
                    makeUniqeId(){
                        return 'inpid'+kabinet.uniqueId();
                    },
                    saveStatus(){
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{this.$root.savetask(this.tindex);},1000);
                    }
                }
            });


            const reportsListApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        task_id:PHPPARAMS['TASK_ID'],
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        showloadmore:true,
                        limitpics:5
                    }
                },
                /*
                watch:{
                    limitpics: {
                        handler(val, oldVal) {
                                console.log(val);
                        },
                        deep: true
                    },
                },
                 */
                setup(){
                    const {projectOrder, projectTask} = data_helper();
                    
                    const hiddenCommentBlock = hiddenCommentBlock_();

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
                    isViewMore(){
                        if(this.total <= this.countview || !this.showloadmore) return false;
                        return true;
                    },
                    StatusWatch(){
                        return this.datarunner.UF_STATUS;
                    },
                    viewedcount(){
                        return this.datarunner.length;
                    },
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

                            if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0){
                                const message_store = messageStore();                                
								for(index in data.MESSAGE_DATA) 
                                    message_store.datamessage[index] = data.MESSAGE_DATA[index];
                            }

                            if (
                                typeof data.RUNNER_DATA != "undefined" &&
                                data.RUNNER_DATA.length == 0
                            )
                                this_.showloadmore = false;

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
                    showall: function (task) {
                        task.LIMIT = 1000;
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
                            this.datarunner[index].UF_REPORT_LINK_ORIGINAL ||
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
                        if (["0","3","8"].indexOf(runner.UF_STATUS) == -1) return false;

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
            });

            configureVueApp(reportsListApplication);
        }
    }
}());