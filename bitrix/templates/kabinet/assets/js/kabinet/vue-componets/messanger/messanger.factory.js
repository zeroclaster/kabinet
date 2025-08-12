const createMessangerSystem = () => {
    // Создаем уникальное имя хранилища для каждого экземпляра
    const storeName = `messagelist_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;

    // Создаем изолированное хранилище
    const messageStore = BX.Vue3.Pinia.defineStore(storeName, {
        state: () => ({ datamessage: [] })
    });

    // Возвращаем конфигурацию для компонента
    return {
        component: {
            start(PHPPARAMS) {

                if (typeof PHPPARAMS.NEW_RESET == "undefined")  PHPPARAMS.NEW_RESET = 'y';
                PHPPARAMS.TEMPLATE = window[PHPPARAMS.TEMPLATE];
                if (typeof PHPPARAMS.FILTER == "undefined")  PHPPARAMS.FILTER = {};

                return BX.Vue3.BitrixVue.mutableComponent(`messanger_${storeName}`, {
                    template: PHPPARAMS.TEMPLATE,
                    data(){
                        return{
                            // поля для отправки сообщения
                            fields:{
                                'ID':0,
                                'UF_PROJECT_ID':0,
                                'UF_TASK_ID':0,
                                'UF_QUEUE_ID':0,
                                'UF_SUBMESS_ID':0,
                                'UF_MESSAGE_TEXT':'',
                                'UF_UPLOADFILE':[],
                                'UF_TARGET_USER_ID':0,
                                'UF_PROJECT_ID_ORIGINAL':0,
                                'UF_TASK_ID_ORIGINAL':0,
                                'UF_QUEUE_ID_ORIGINAL':0,
                                'UF_SUBMESS_ID_ORIGINAL':0,
                                'UF_MESSAGE_TEXT_ORIGINAL':'',
                                'UF_UPLOADFILE_ORIGINAL':null,
                                'UF_TARGET_USER_ID_ORIGINAL':0,
                            },
                            offset:0,
                            limit:PHPPARAMS.VIEW_COUNT,
                            new_reset: PHPPARAMS.NEW_RESET,
                        }
                    },
                    props: ['projectID','taskID','queue_id','targetUserID','isMobile'],
                    computed: {
                        ...BX.Vue3.Pinia.mapState(messageStore, ['datamessage']),
                        ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                        ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                        ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
                        ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                        // рапрещаем кнопку отправить для след случаев
                        isDisabled(){
                            if(this.fields.UF_MESSAGE_TEXT == '' && this.fields.UF_UPLOADFILE.length==0) return true;

                            return false;
                        },
                        projectlist(){
                            let projectById = {};
                            this.data.forEach((element)=>{
                                projectById[element.ID] = element;
                            });

                            return projectById;
                        },
                        tasklist(){
                            let taskById = {};
                            this.datatask.forEach((element)=>{
                                taskById[element.ID] = element;
                            });

                            return taskById;
                        },
                        datamessageForYou(){
                            let data = [];
                            this.datamessage.forEach((element)=>{
                                if (element.UF_TARGET_USER_ID == this.datauser.ID) data.push(element);
                            });

                            return data;
                        },
                    },
                    watch:{
                    },
                    mounted () {
                        // Add event handler
                        const this_ = this;

                        this.fields.UF_PROJECT_ID = this.projectID;
                        this.fields.UF_TASK_ID = this.taskID;
                        this.fields.UF_QUEUE_ID = this.queue_id;
                        this.fields.UF_TARGET_USER_ID = this.targetUserID;

                        //let node = this.$refs.textares;
                        this.scrollEnd();

                    },
                    methods: {
                        ...addNewMethods(),
                        addNewMethods_(){
                            var form_data =  this.dataToFormData(this.fields);
                            form_data.append("OFFSET", this.offset);
                            form_data.append("LIMIT", this.limit);

                            return form_data;
                        },
                        isNewMessage(mess_item){
                            if (mess_item.UF_STATUS == 5) return 'new-message';
                            return '';
                        },
                        printStatus(message){

                            if (message.UF_AUTHOR_ID != this.datauser.ID) return '';
                            if (message.UF_STATUS == 5) return '<div class="mdi-check"></div>';

                            return '<div class="mdi-check-all"></div>';
                        },
                        accessAction(message){
                            if (message.UF_AUTHOR_ID == this.datauser.ID && message.UF_STATUS == 5) return true;
                            return false;
                        },
                        closeAllcanselEdit(){
                            const node = this.$refs.messagelist;
                            const listBlock = BX.findChild(node,{class:'cansel-edit'},true,true);
                            if (!listBlock) return;
                            listBlock.forEach((element)=>{
                                BX.hide(element);
                            });
                        },
                        canseledit(e){
                            this.closeAllcanselEdit();

                            this.fields.ID = 0;
                            this.fields.UF_MESSAGE_TEXT = '';
                            this.fields.UF_MESSAGE_TEXT_ORIGINAL = '';
                            this.$refs.richtextref.clear();

                        },
                        // Ответить на сообщение
                        answermess(item,e){
                            const editor = this.$refs.richtextref.$.ckEditor;
                            editor.model.document.once('change:data', (evt, data) => {
                                this.$refs.richtextref.focusEditor();
                            });
                            const htmlquote = '<blockquote>'+'<p>'+item.UF_AUTHOR_ID_ORIGINAL.PRINT_NAME+'</p><p>'+item.UF_PUBLISH_DATE_ORIGINAL.FORMAT3+'</p><p>Написал:</p>'+item.UF_MESSAGE_TEXT+'</blockquote>';
                            this.fields.UF_MESSAGE_TEXT = htmlquote;
                            this.fields.UF_MESSAGE_TEXT_ORIGINAL = htmlquote;

                            const node = BX.findChild(this.$refs.senderblock,{class:'ck-content'},true,false);
                            kabinet.gotoElement(node);
                        },
                        //Редактировать сообщеине
                        editmess(item,e){
                            const node = BX.findChild(e.target.parentNode,{class:'cansel-edit'},true,false);
                            BX.show(node);

                            // если установлен ID то запись в таблице не создается а редактируется
                            this.fields.ID = item.ID;
                            this.fields.UF_MESSAGE_TEXT = item.UF_MESSAGE_TEXT;
                            this.fields.UF_MESSAGE_TEXT_ORIGINAL = item.UF_MESSAGE_TEXT_ORIGINAL;
                        },
                        //Удалить сообщеине
                        removemess(ID){
                            kabinet.loading();
                            var form_data =  this.addNewMethods_(this.fields);
                            for(itm in PHPPARAMS.FILTER){
                                form_data.append("FILTER-"+itm, PHPPARAMS.FILTER[itm]);
                            }

                            form_data.append("ID", ID);
                            form_data.append('action', 'removemess');
                            const kabinetStore = usekabinetStore();
                            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.removemess', {
                                data : form_data,
                                // usr_id_const нужен для админа, задается в footer.php
                                getParameters: {usr : usr_id_const},
                                //processData: false,
                                //preparePost: false
                            })
                                .then(BX.delegate(this.responseHandler, this), BX.delegate(this.errorHandler, this));
                        },
                        removeUplFile(fileIndex){
                            const dt = new DataTransfer();
                            let arr_files = [...this.fields.UF_UPLOADFILE];
                            arr_files.forEach((f, i)=>{
                                if (i != fileIndex)
                                    dt.items.add(f);
                            });

                            this.fields.UF_UPLOADFILE = dt.files;
                        },
                        responseHandler(response){

                            const data = response.data;
                            const kabinetStore = usekabinetStore();
                            const Store = messageStore();
                            kabinetStore.NotifyOk = '';
                            kabinetStore.NotifyOk = data.message;
                            // если на странице согласования и отчеты, то чат в пределах задачи
                            if (this.queue_id) {
                                if (data.action == 'showmore')
                                    if (data.datamessage.length == Store.datamessage[this.queue_id].length) BX.hide(this.$refs.showmoreblock);

                                Store.datamessage[this.queue_id] = data.datamessage;
                            }else if(this.projectID){
                                if (data.action == 'showmore')
                                    if (data.datamessage.length == Store.datamessage[this.projectID].length) BX.hide(this.$refs.showmoreblock);

                                Store.datamessage[this.projectID] = data.datamessage;
                            }
                            else{
                                if (data.action == 'showmore')
                                    if (data.datamessage.length == Store.datamessage.length) BX.hide(this.$refs.showmoreblock);

                                Store.datamessage = data.datamessage;
                            }

                            this.fields.ID = 0;
                            this.fields.UF_MESSAGE_TEXT = '';
                            this.fields.UF_UPLOADFILE = [];
                            this.fields.UF_MESSAGE_TEXT_ORIGINAL = '';
                            this.fields.UF_SUBMESS_ID = 0;
                            this.fields.UF_SUBMESS_ID_ORIGINAL = 0;

                            if (this.$refs.richtextref) {
                                this.$refs.richtextref.clear();
                                this.scrollEnd();
                                this.closeAllcanselEdit();
                            }

                            //console.log(response)
                            kabinet.loading(false);
                        },
                        errorHandler(response){
                            const kabinetStore = usekabinetStore();
                            //console.log(response);
                            kabinet.loading(false);
                            response.errors.forEach((error) => {
                                if (response.errors[0].code != 0) {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = response.errors[0].message;
                                }else {
                                    kabinetStore.Notify = '';
                                    kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                                }
                            });

                        },
                        showMore(){
                            kabinet.loading();
                            this.limit = parseInt(this.limit) + 5;
                            var form_data = this.addNewMethods_(this.fields);
                            form_data.append("NEW_RESET", this.new_reset);
                            form_data.append('action', 'showmore');
                            for(itm in PHPPARAMS.FILTER){
                                form_data.append("FILTER-"+itm, PHPPARAMS.FILTER[itm]);
                            }
                            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.showmore', {
                                data : form_data,
                                // usr_id_const нужен для админа, задается в footer.php
                                getParameters: {usr : usr_id_const},
                                //processData: false,
                                //preparePost: false
                            }).then(BX.delegate(this.responseHandler, this), BX.delegate(this.errorHandler, this));
                        },
                        scrollEnd(){
                            const node = this.$refs.messagelist;
                            if (typeof node != 'undefined') {
                                setTimeout(()=>{
                                    node.scrollTop = node.scrollHeight;
                                },200);
                            }
                        },
                        sendMessage(e){
                            kabinet.loading();
                            this.limit = parseInt(this.limit) + 1;
                            var form_data =  this.addNewMethods_(this.fields);
                            for(itm in PHPPARAMS.FILTER){
                                form_data.append("FILTER-"+itm, PHPPARAMS.FILTER[itm]);
                            }
                            if (this.fields.ID)  form_data.append('action', 'editmessage');
                            else  form_data.append('action', 'newmessage');
                            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.newmessage', {
                                data : form_data,
                                // usr_id_const нужен для админа, задается в footer.php
                                getParameters: {usr : usr_id_const},
                                //processData: false,
                                //preparePost: false
                            })
                                .then(BX.delegate(this.responseHandler, this), BX.delegate(this.errorHandler, this));
                        },
                    },
                    components: {
                        richtext,
                        messUploadFileComponent,
                    }
                });
            }
        },
        store: messageStore
    };
};