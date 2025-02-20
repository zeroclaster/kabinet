var messanger_vuecomponent = document.messanger_vuecomponent || {};
messanger_vuecomponent = (function (){
    return {
        start(PHPPARAMS){
          if (typeof PHPPARAMS.NEW_RESET == "undefined")  PHPPARAMS.NEW_RESET = 'y';
          if (typeof PHPPARAMS.TEMPLATE == "undefined")  PHPPARAMS.TEMPLATE = messangerTemplate;
          if (typeof PHPPARAMS.messageStore == "undefined")  PHPPARAMS.messageStore = messageStore;
       /*
       messangerTemplate задается в bitrix/templates/kabinet/assets/js/kabinet/custom.component.js
       определяется атребутом data-usermessanger
        */
return BX.Vue3.BitrixVue.mutableComponent('messanger_comp', {
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
    props: ['projectID','taskID','queue_id','targetUserID'],
    computed: {
		...BX.Vue3.Pinia.mapState(PHPPARAMS.messageStore, ['datamessage']),
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
			
			this.fields.ID = item.ID;
			this.fields.UF_MESSAGE_TEXT = item.UF_MESSAGE_TEXT;
			this.fields.UF_MESSAGE_TEXT_ORIGINAL = item.UF_MESSAGE_TEXT_ORIGINAL;
		},
        //Удалить сообщеине
		removemess(ID){
            const this_ = this;
            kabinet.loading();
            var form_data = new FormData();

			form_data.append("ID", ID);

            for ( var key in this.fields) {

                if (key == "ID") continue;

                if (key == "UF_UPLOADFILE") {
                    if (this.fields["UF_UPLOADFILE"].length == 0) form_data.append(key + '[]', 0);
                    for (const file of this.fields["UF_UPLOADFILE"]) {
                        form_data.append(key + '[]', file);
                    }
                } else {

                    if (Array.isArray(this.fields[key])) {
                        this.fields[key].forEach(function (item, index) {
                            form_data.append(key + '[]', item.VALUE);
                        });
                    } else
                        form_data.append(key, this.fields[key]);
                }
            }

            form_data.append("OFFSET", this.offset);
            form_data.append("LIMIT", this.limit);
            const kabinetStore = usekabinetStore();
            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.removemess', {
                data : form_data,
                // usr_id_const нужен для админа, задается в footer.php
                getParameters: {usr : usr_id_const},
                //processData: false,
                //preparePost: false
            })
                .then(function(response) {
                    const data = response.data;

                    /*
                    let messages;
                    if (this_.queue_id) {
                        messages = this_.datamessage[this_.queue_id];
                    }else{
                        messages = this_.datamessage;
                    }

                    for (index in data.datamessage) messages[index] = data.datamessage[index];
                     */

                    const Store = messageStore();
                    // если на странице согласования и отчеты, то чат в пределах задачи
                    if (this_.queue_id) {
                        Store.datamessage[this_.queue_id] = data.datamessage;
                    }else{
                        Store.datamessage = data.datamessage;
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
        removeUplFile(fileIndex){
            const dt = new DataTransfer();
            let arr_files = [...this.fields.UF_UPLOADFILE];
            arr_files.forEach((f, i)=>{
                if (i != fileIndex)
                dt.items.add(f);
            });

            this.fields.UF_UPLOADFILE = dt.files;
        },
        showMore(){
            const this_ = this;
            kabinet.loading();
            var form_data = new FormData();

            for ( var key in this.fields) {
                if (key == "UF_UPLOADFILE") {
                    if (this.fields["UF_UPLOADFILE"].length == 0) form_data.append(key + '[]', 0);
                    for (const file of this.fields["UF_UPLOADFILE"]) {
                        form_data.append(key + '[]', file);
                    }
                } else {

                    if (Array.isArray(this.fields[key])) {
                        this.fields[key].forEach(function (item, index) {
                            form_data.append(key + '[]', item.VALUE);
                        });
                    } else
                        form_data.append(key, this.fields[key]);
                }
            }

            this.limit = parseInt(this.limit) + 5;
            form_data.append("OFFSET", this.offset);
            form_data.append("LIMIT", this.limit);
            form_data.append("NEW_RESET", this.new_reset);
            const kabinetStore = usekabinetStore();
            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.showmore', {
                data : form_data,
                // usr_id_const нужен для админа, задается в footer.php
                getParameters: {usr : usr_id_const},
                //processData: false,
                //preparePost: false
            })
                .then(function(response) {
                    const data = response.data;

                    const Store = messageStore();
                    // если на странице согласования и отчеты, то чат в пределах задачи
                    if (this_.queue_id) {
                        if (data.datamessage.length == Store.datamessage[this_.queue_id].length) BX.hide(this_.$refs.showmoreblock);
                        else Store.datamessage[this_.queue_id] = data.datamessage;
                    }else{
                        if (data.datamessage.length == Store.datamessage.length) BX.hide(this_.$refs.showmoreblock);
                        else Store.datamessage = data.datamessage;
                    }

                    /*
                    let messages = null;
                    if (this_.queue_id) messages = this_.datamessage[this_.queue_id];
                    else messages = this_.datamessage;

                    if (data.datamessage.length == messages.length) {
                        BX.hide(this_.$refs.showmoreblock);
                    }else {
                            for (index in data.datamessage) messages[index] = data.datamessage[index];
                    }
                     */

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
        scrollEnd(){
            const node = this.$refs.messagelist;
			if (typeof node != 'undefined') {
				setTimeout(()=>{
					node.scrollTop = node.scrollHeight;
				},200);
			}
        },
        sendMessage(e){
            const this_ = this;

            kabinet.loading();

            var form_data = new FormData();
            for ( var key in this.fields) {
                if (key == "UF_UPLOADFILE") {
                    if (this.fields["UF_UPLOADFILE"].length == 0) form_data.append(key + '[]', '');
                    for (const file of this.fields["UF_UPLOADFILE"]) {
                        form_data.append(key + '[]', file);
                    }
                } else {

                    if (Array.isArray(this.fields[key])) {
                        this.fields[key].forEach(function (item, index) {
                            form_data.append(key + '[]', item.VALUE);
                        });
                    } else
                        form_data.append(key, this.fields[key]);
                }
            }

            this.limit = parseInt(this.limit) + 1;
            form_data.append("OFFSET", this.offset);
            form_data.append("LIMIT", this.limit);

            const kabinetStore = usekabinetStore();
            BX.ajax.runAction('bitrix:kabinet.evn.messengerevents.newmessage', {
                data : form_data,
                // usr_id_const нужен для админа, задается в footer.php
                getParameters: {usr : usr_id_const},
                //processData: false,
                //preparePost: false
            })
                .then(function(response) {
                    const data = response.data;
                    kabinetStore.NotifyOk = '';
                    kabinetStore.NotifyOk = data.message;

                    // предыдущий метод обновления после отправки
                    /*
                    let messages;
                    if (this_.queue_id) {
                        messages = this_.datamessage[this_.queue_id];
                    }else{
                        messages = this_.datamessage;
                    }
                     */

                    //for (index in data.datamessage) messages[index] = data.datamessage[index];

                    const Store = messageStore();
                    // если на странице согласования и отчеты, то чат в пределах задачи
                    if (this_.queue_id) {
                        Store.datamessage[this_.queue_id] = data.datamessage;
                    }else{
                        Store.datamessage = data.datamessage;
                    }

                    this_.fields.UF_MESSAGE_TEXT = '';
                    this_.fields.UF_UPLOADFILE = [];
                    this_.fields.UF_MESSAGE_TEXT_ORIGINAL = '';
                    this_.fields.UF_SUBMESS_ID = 0;
                    this_.fields.UF_SUBMESS_ID_ORIGINAL = 0;
                    this_.$refs.richtextref.clear();

                    this_.scrollEnd();
					this_.closeAllcanselEdit();

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
    },
	components: {
		richtext,
        messUploadFileComponent,
	}
});


        }
    }
}());