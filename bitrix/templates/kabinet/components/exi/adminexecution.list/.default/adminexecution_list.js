var adminexecution_list = document.adminclient_list || {};
adminexecution_list = (function (){
    return {
        start(PHPPARAMS, messageStoreInstance){

const changenotes = BX.Vue3.BitrixVue.mutableComponent('change-notes', {
    template: `
        <div class="mb-3 form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0" :for="'notes-execution'+id_input">Заметки</label>
                <button 
                    v-if="!isEditing && notesFulfiList.length > 0" 
                    class="btn btn-outline-secondary btn-sm"
                    @click="startEditing"
                    title="Редактировать заметку"
                >
                    <i class="fa fa-edit"></i>
                </button>
            </div>
            
            <!-- Режим просмотра -->
            <div v-if="!isEditing" class="notes-container">
                <div 
                    v-if="notesFulfiList.length > 0" 
                    class="note-sticker"
                >
                    <div class="note-content">
                        <div class="note-history">
                            <div v-for="(note, index) in notesFulfiList" :key="index" class="note-item">
                                <div class="note-meta">
                                    <span class="note-date">{{ formatDate(note.date) }}</span>
                                    <span class="note-user">{{ note.username }}</span>
                                    <!-- Кнопка редактирования для каждой заметки -->
                                    <button 
                                        v-if="!isEditingNote || editingNoteIndex !== index"
                                        class="btn btn-link btn-sm p-0 ml-2" 
                                        @click.stop="editSpecificNote(note, index)"
                                        title="Редактировать эту заметку"
                                    >
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <!-- Индикатор редактируемой заметки -->
                                    <span v-if="editingNoteIndex === index" class="badge bg-warning ml-2">
                                        Редактируется
                                    </span>
                                </div>
                                <div class="note-text">{{ note.text }}</div>
                            </div>
                        </div>
                        <div class="note-corner">
                            <i class="fa fa-paperclip"></i>
                        </div>
                    </div>
                </div>
                <div 
                    v-else 
                    class="note-placeholder"
                    @click="startEditing"
                >
                    <div class="placeholder-content">
                        <i class="fa fa-plus-circle"></i>
                        <span>Добавить заметку</span>
                    </div>
                </div>
            </div>
            
            <!-- Режим редактирования -->
            <div v-else class="notes-edit">
                <div class="note-sticker editing">
                    <div class="note-content">
                        <div v-if="editingNoteIndex !== null" class="mb-2 text-muted small">
                            Редактирование заметки от {{ formatDate(notesFulfiList[editingNoteIndex].date) }}
                        </div>
                        <textarea 
                            class="form-control note-textarea" 
                            :id="'notes-execution'+id_input" 
                            v-model="noteText" 
                            :placeholder="editingNoteIndex !== null ? 'Редактировать заметку...' : 'Введите текст заметки...'"
                            rows="4"
                            ref="textareaRef"
                        ></textarea>
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2 justify-content-end">
                    <button 
                        class="btn btn-success btn-sm" 
                        @click="saveNote" 
                        :disabled="!noteText.trim()"
                    >
                        <i class="fa fa-check"></i> 
                        {{ editingNoteIndex !== null ? 'Обновить' : 'Сохранить' }}
                    </button>
                    <button 
                        v-if="editingNoteIndex !== null"
                        class="btn btn-danger btn-sm" 
                        @click="deleteNote"
                        title="Удалить заметку"
                    >
                        <i class="fa fa-trash"></i> Удалить
                    </button>
                    <button 
                        class="btn btn-outline-secondary btn-sm" 
                        @click="cancelEditing"
                    >
                        <i class="fa fa-times"></i> Отмена
                    </button>
                </div>
            </div>
        </div>
    `,
    data(){
        return{
            id_input: 'inpid'+kabinet.uniqueId(),
            noteText: '',
            isEditing: false,
            editingNoteIndex: null, // Индекс редактируемой заметки
            notesFulfiList: []
        }
    },
    props: ['fulfillmentId'],
    computed: {
        ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
    },
    mounted() {
        this.loadCurrentNote();
    },
    methods: {
        loadCurrentNote() {
            if (!this.fulfillmentId) return;

            const this_ = this;
            BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.getcurrentnote', {
                data: {
                    fulfillment_id: this.fulfillmentId
                }
            }).then(function(response) {
                if (response.data && response.data.note) {
                    try {
                        // Парсим JSON строку из сервера
                        const parsedData = parseJSON(response.data.note);

                        if (Array.isArray(parsedData)) {
                            this_.notesFulfiList = parsedData;
                        } else if (parsedData && typeof parsedData === 'object') {
                            // Если пришел объект, преобразуем в массив
                            this_.notesFulfiList = [parsedData];
                        } else {
                            this_.notesFulfiList = [];
                        }
                    } catch (e) {
                        console.error('Error parsing notes data:', e);
                        this_.notesFulfiList = [];
                    }
                } else {
                    this_.notesFulfiList = [];
                }
            }).catch(function(error) {
                console.error('Error loading notes:', error);
                this_.notesFulfiList = [];
            });
        },

        // Редактирование конкретной заметки
        editSpecificNote(note, index) {
            this.editingNoteIndex = index;
            this.isEditing = true;
            this.noteText = note.text;

            this.$nextTick(() => {
                if (this.$refs.textareaRef) {
                    this.$refs.textareaRef.focus();
                    this.$refs.textareaRef.style.height = 'auto';
                    this.$refs.textareaRef.style.height = this.$refs.textareaRef.scrollHeight + 'px';
                }
            });
        },

        startEditing() {
            this.isEditing = true;
            this.editingNoteIndex = null; // Сбрасываем индекс при добавлении новой заметки
            this.noteText = '';

            this.$nextTick(() => {
                if (this.$refs.textareaRef) {
                    this.$refs.textareaRef.focus();
                    this.$refs.textareaRef.style.height = 'auto';
                    this.$refs.textareaRef.style.height = this.$refs.textareaRef.scrollHeight + 'px';
                }
            });
        },

        cancelEditing() {
            this.isEditing = false;
            this.noteText = '';
            this.editingNoteIndex = null;
        },

        // Удаление заметки
        deleteNote() {
            if (this.editingNoteIndex === null) return;
            
            if (!confirm('Вы уверены, что хотите удалить эту заметку?')) return;

            // Удаляем заметку из списка
            const updatedNotesList = this.notesFulfiList.filter((_, index) => index !== this.editingNoteIndex);
            
            this.saveNotesToServer(updatedNotesList);
        },

        saveNote() {
            if (!this.noteText.trim()) return;

            let updatedNotesList;

            if (this.editingNoteIndex !== null) {
                // Редактирование существующей заметки
                updatedNotesList = [...this.notesFulfiList];
                updatedNotesList[this.editingNoteIndex] = {
                    ...updatedNotesList[this.editingNoteIndex],
                    text: this.noteText.trim(),
                    edited_date: new Date().toISOString(), // Добавляем дату редактирования
                    edited_by: this.datauser.PRINT_NAME || 'ADMIN'
                };
            } else {
                // Создаем новую заметку
                const newNote = {
                    username: this.datauser.PRINT_NAME || 'ADMIN',
                    date: new Date().toISOString(),
                    text: this.noteText.trim()
                };
                // Добавляем новую заметку к существующему списку
                updatedNotesList = [...this.notesFulfiList, newNote];
            }

            this.saveNotesToServer(updatedNotesList);
        },

        // Общий метод для сохранения заметок на сервер
        saveNotesToServer(updatedNotesList) {
            const this_ = this;
            const kabinetStore = usekabinetStore();

            // Преобразуем в JSON строку для отправки на сервер
            const notesJsonString = JSON.stringify(updatedNotesList);

            BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.savenote', {
                data: {
                    fulfillment_id: this.fulfillmentId,
                    note_text: notesJsonString
                }
            }).then(function(response) {
                if (response.data.success) {
                    kabinetStore.NotifyOk = '';
                    kabinetStore.NotifyOk = this_.editingNoteIndex !== null ? 'Заметка обновлена' : 'Заметка сохранена';
                    this_.notesFulfiList = updatedNotesList; // Обновляем локальный список
                    this_.noteText = '';
                    this_.isEditing = false;
                    this_.editingNoteIndex = null;
                } else {
                    kabinetStore.Notify = response.data.message || 'Ошибка при сохранении заметки';
                }
            }).catch(function(response) {
                kabinetStore.Notify = '';
                kabinetStore.Notify = 'Ошибка при сохранении заметки';
                console.error('Save note error:', response);
            });
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
});

            // Добавляем компонент для заголовка с сортировкой
            const SortableHeader = BX.Vue3.BitrixVue.mutableComponent('sortable-header', {
                template: `
                    <th scope="col" class="sortable-header" @click="toggleSort">
                        <span class="header-text">{{fieldTitle}}</span>
                        <span class="sort-arrows">
                            <i class="fa fa-arrow-up" :class="{ active: sortField === fieldName && sortOrder === 'asc' }"></i>
                            <i class="fa fa-arrow-down" :class="{ active: sortField === fieldName && sortOrder === 'desc' }"></i>
                        </span>
                    </th>
                `,
                props: ['currentSortField', 'currentSortOrder','fieldName','fieldTitle'],
                computed: {
                    sortField: {
                        get() { return this.currentSortField; },
                        set(value) { this.$emit('update:currentSortField', value); }
                    },
                    sortOrder: {
                        get() { return this.currentSortOrder; },
                        set(value) { this.$emit('update:currentSortOrder', value); }
                    }
                },
                methods: {
                    toggleSort() {
                        if (this.sortField === this.fieldName) {
                            // Если уже сортируем по этому полю, меняем направление
                            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                        } else {
                            // Если сортируем по другому полю, переключаемся на это поле
                            this.sortField = this.fieldName;
                            this.sortOrder = 'desc';
                        }
                        this.$root.applySort();
                    }
                }
            });


            const changestatus = BX.Vue3.BitrixVue.mutableComponent('change status', {
                template: `
        <div class="mt-3" v-if="catalog.length>0">
            <div class="h4">Сменить статус:</div>
            <div class="form-group select-status" v-for="Status in catalog">
                <div class="form-check">
                    <input 
                        @change="saveStatus(Status.ID)" 
                        :name="$id('name')" 
                        class="form-check-input" 
                        :id="'status-'+Status.ID+'-'+tindex" 
                        :checked="localModelValue == Status.ID" 
                        type="radio" 
                        :value="Status.ID"
                    >
                    <label class="form-check-label text-primary" :for="'status-'+Status.ID+'-'+tindex">{{Status.TITLE}}</label>
                </div>
            </div>
        </div>
    `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId(),
                        localModelValue: 0
                    }
                },
                props: ['modelValue', 'tindex', 'catalog'],
                computed: {
                },
                watch: {
                    // Следим за изменением modelValue извне
                    modelValue: {
                        immediate: true,
                        handler(newVal) {
                            this.localModelValue = newVal || 0;
                        }
                    }
                },
                mounted() {
                    const this_ = this;
                    this.localModelValue = this.modelValue || 0;

                    this.$.iid = function() {
                        return "uid-" + kabinet.uniqueId();
                    };
                },
                methods: {
                    makeUniqeId(){
                        return 'inpid'+kabinet.uniqueId();
                    },
                    saveStatus(statusId){
                        // Устанавливаем новое значение
                        this.localModelValue = statusId;

                        // Сохраняем в родительский компонент
                        this.$emit('update:modelValue', statusId);

                        // Запускаем сохранение с задержкой
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(() => {
                            this.$root.datarunner[this.tindex].UF_STATUS = statusId;
                            this.$root.savetask(this.tindex);

                            // Если выбрали статус 0 (Запланирован), очищаем локальное значение
                            // чтобы можно было снова выбрать тот же статус
                            if (statusId === 0) {
                                setTimeout(() => {
                                    this.localModelValue = -1; // Устанавливаем в -1 чтобы сбросить выбор
                                }, 100);
                            }
                        }, 1000);
                    }
                }
            });

            const changeResponsible = BX.Vue3.BitrixVue.mutableComponent('change-Responsible', {
                template: `
                <div><label class="" :for="'responsible-admin'+id_input">Ответственный</label></div>
                <div>
                    <select :id="'responsible-admin'+id_input" v-model="responsibleModelvalue">
                        <option></option>
                        <option v-for="option in admindata" :value="option.id">{{ option.value }}</option>
                    </select>
                </div>
                `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId(),
                    }
                },
                props: ['modelValue','tindex','admindata','status'],
                computed: {
                    responsibleModelvalue: {
                        get() {
                            if (!this.modelValue) return 0;

                            try {
                                const data = JSON.parse(this.modelValue);
                                if (Array.isArray(data) && data.length > 0) {
                                    // Ищем последнюю запись с текущим статусом
                                    const currentResponsible = data.find(item => item.status === this.status);
                                    if (currentResponsible) {
                                        return currentResponsible.id;
                                    }

                                    // Если нет записи с текущим статусом, возвращаем последнюю
                                    //return data[data.length - 1].id;
                                    return 0;
                                }
                            } catch (e) {
                                console.error('Error parsing UF_RESPONSIBLE:', e);
                            }

                            return 0;
                        },
                        set(newValue) {
                            if (!newValue || newValue === 0) {
                                this.$emit('update:modelValue', '[]');
                                return;
                            }

                            const user = this.admindata.find(t => t.id === newValue);
                            if (!user) return;

                            let data = [];

                            try {
                                if (this.modelValue) {
                                    data = JSON.parse(this.modelValue);
                                }
                            } catch (e) {
                                console.error('Error parsing existing UF_RESPONSIBLE:', e);
                                data = [];
                            }

                            // Удаляем существующую запись с текущим статусом (если есть)
                            data = data.filter(item => item.status !== this.status);

                            // Добавляем новую запись
                            data.push({
                                id: user.id,
                                value: user.value,
                                status: this.status,
                                date: new Date().toISOString()
                            });

                            this.$emit('update:modelValue', JSON.stringify(data));
                            this.inpsave(this.tindex);
                        }
                    }
                },
                methods: {
                    inpsave(index){
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{this.$root.savetask(index);},1000);
                    }
                }
            });

            const accountfield = BX.Vue3.BitrixVue.mutableComponent('account-field', {
                template: `
                            <div class="mb-3 form-group">
                <div><label class="" :for="'accaunt-execution'+id_input">Имя аккаунта</label></div>
                <div><input class="form-control" type="text" :id="'accaunt-execution'+id_input" v-model="accauntModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'login-execution'+id_input">Логин</label></div>
                <div><input class="form-control" type="text" :id="'login-execution'+id_input" v-model="loginModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'pass-execution'+id_input">Пароль</label></div>
                <div><input class="form-control" type="text" :id="'pass-execution'+id_input" v-model="passModelvalue" @input="inpsave(tindex)"></div>
            </div>
            <div class="mb-3 form-group">
                <div><label class="" :for="'ip-execution'+id_input">IP размещения</label></div>
                <div><input class="form-control" type="text" :id="'ip-execution'+id_input" v-model="ipModelvalue" @input="inpsave(tindex)"></div>
            </div>
                `,
                data(){
                    return{
                        id_input:'inpid'+kabinet.uniqueId(),
                    }
                },
                props: ['modelValue','tindex'],
                computed: {
                    accauntModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.accaunt;
                        },
                        set(newValue) {
                            this.$.fields.accaunt = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    loginModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.login;
                        },
                        set(newValue) {
                            this.$.fields.login = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    passModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.pass;
                        },
                        set(newValue) {
                            this.$.fields.pass = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                    ipModelvalue:{
                        get() {
                            if (!this.modelValue) this.$.fields = this.createfields();
                            else this.$.fields = {...parseJSON( this.modelValue )};
                            return this.$.fields.ip;
                        },
                        set(newValue) {
                            this.$.fields.ip = newValue;
                            this.$emit('update:modelValue', JSON.stringify(this.$.fields))
                        },
                    },
                },
                mounted () {
                    // Add event handler
                    const this_ = this;
                },
                methods: {
                    createfields(){
                        return {'accaunt':'','login':'','pass':'','ip':''};
                    },
                    inpsave(index){
                        if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
                        this.$.inpSaveTimer = setTimeout(()=>{this.$root.savetask(index);},1000);
                    }
                }
            });


            const adminClientListApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        countview:PHPPARAMS['viewcount'],
                        total: PHPPARAMS['total'],
                        adminlist: PHPPARAMS['adminlist'],
                        showloadmore:true,
                        limitpics:5,
                        sort_field: PHPPARAMS['sort_field'] || 'UF_PLANNE_DATE',
                        sort_order: PHPPARAMS['sort_order'] || 'asc'
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(clientlistStore, ['dataclient']),
                    ...BX.Vue3.Pinia.mapState(projectlistStore, ['dataproject']),
                    ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                    ...BX.Vue3.Pinia.mapState(orderlistStore, ['dataorder']),
                    ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner']),
                    ...BX.Vue3.Pinia.mapState(cataloglistStore, ['data3']),
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                    viewedcount(){
                        return this.datarunner.length;
                    },
                },
                methods: {
                    ...helperVueComponents(),
                    showhidehistory(e){
                        const node = BX.findChild(e.target.parentNode,{class:'history-list'},true,false);
                        if (e.target.checked) BX.show(node);
                        else  BX.hide(node);
                    },
                    displayCurrentStatus(runner){
                        let ret = '';
                        for(element of runner.STATUSLIST) if (element.ID == runner.UF_STATUS) ret = element.TITLE
                        return ret;
                    },

                    applySort() {
                        // Перезагружаем данные с новой сортировкой
                        this.reloadData();
                    },

                    reloadData() {
                        const this_ = this;
                        let formData = new FormData;

                        // Сбрасываем offset при смене сортировки
                        this.$root.offset = 0;
                        formData.append("OFFSET", this.$root.offset);
                        formData.append("FILTER_JSON", JSON.stringify(filterclientlist));
                        formData.append("countview", this_.countview);

                        // Добавляем параметры сортировки
                        formData.append("sort_field", this_.sort_field);
                        formData.append("sort_order", this_.sort_order);

                        const kabinetStore = usekabinetStore();
                        kabinet.loading();

                        // Очищаем текущие данные
                        this_.datarunner.length = 0;

                        var data = BX.ajax.runComponentAction("exi:adminexecution.list", "loadmore", {
                            mode: 'class',
                            data: formData,
                            timeout: 300
                        }).then(function (response) {
                            kabinet.loading(false);
                            const data = response.data;

                            // Обработка данных как в методе moreload
                            if (typeof data.RUNNER_DATA != "undefined" && data.RUNNER_DATA.length == 0) this_.showloadmore = false;

                            if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0 && messageStoreInstance){
                                for(index in data.MESSAGE_DATA) {
                                    messageStoreInstance.datamessage[index] = data.MESSAGE_DATA[index];
                                }
                            }

                            if (typeof data.CLIENT_DATA != "undefined")
                                for(index in data.CLIENT_DATA) {
                                    this_.dataclient[index] = data.CLIENT_DATA[index];
                                }

                            if (typeof data.PROJECT_DATA != "undefined")
                                for(index in data.PROJECT_DATA) {
                                    this_.dataproject[index] = data.PROJECT_DATA[index];
                                }

                            if (typeof data.TASK_DATA != "undefined")
                                for(index in data.TASK_DATA) {
                                    this_.datatask[index] = data.TASK_DATA[index];
                                }

                            if (typeof data.ORDER_DATA != "undefined")
                                for(index in data.ORDER_DATA) {
                                    this_.dataorder[index] = data.ORDER_DATA[index];
                                }

                            if (typeof data.RUNNER_DATA != "undefined"){
                                data.RUNNER_DATA.forEach((elm)=>{this_.datarunner.push(elm)});
                            }

                        }, function (response) {
                            kabinet.loading(false);
                            if (response.errors[0].code != 0) {
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = response.errors[0].message;
                            } else {
                                kabinetStore.Notify = '';
                                kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                            }
                        });
                    },

                    moreload:function (e) {
                        const this_ = this;
                        let formData = new FormData;
                        this.$root.offset = this.$root.offset + 25;
                        formData.append("OFFSET",this.$root.offset);
                        formData.append("FILTER_JSON", JSON.stringify(filterclientlist));
                        formData.append("countview",this_.countview);

                        // Добавляем параметры сортировки
                        formData.append("sort_field", this_.sort_field);
                        formData.append("sort_order", this_.sort_order);

                        const kabinetStore = usekabinetStore();
                        kabinet.loading();
                        var data = BX.ajax.runComponentAction("exi:adminexecution.list", "loadmore", {
                            mode: 'class',
                            data: formData,
                            timeout: 300
                        }).then(function (response) {
                            kabinet.loading(false);
                            const data = response.data;

                            if (typeof data.RUNNER_DATA != "undefined" && data.RUNNER_DATA.length == 0) this_.showloadmore = false;
                            if (this_.dataclient.length == this_.total) this_.showloadmore = false;
                            //if (Object.keys(data.RUNNER_DATA).length == this_.total) this_.showloadmore = false;


                            /*
							if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0){
								const message_store = messageStore();								
								for(index in data.MESSAGE_DATA) 
                                    message_store.datamessage[index] = data.MESSAGE_DATA[index];
							}
                             */

                            // ИСПРАВЛЕННЫЙ БЛОК - используем переданное хранилище
                            if (typeof data.MESSAGE_DATA != "undefined" && Object.keys(data.MESSAGE_DATA).length>0 && messageStoreInstance){
                                for(index in data.MESSAGE_DATA) {
                                    messageStoreInstance.datamessage[index] = data.MESSAGE_DATA[index];
                                }
                            }


                            // клиенты
                            if (typeof data.CLIENT_DATA != "undefined")
                                for(index in data.CLIENT_DATA) {
                                    this_.dataclient[index] = data.CLIENT_DATA[index];
                            }

                            //проекты
                            if (typeof data.PROJECT_DATA != "undefined")
                                for(index in data.PROJECT_DATA) {
                                    this_.dataproject[index] = data.PROJECT_DATA[index];
                            }

                            // задачи
                            if (typeof data.TASK_DATA != "undefined")
                                for(index in data.TASK_DATA) {
                                    this_.datatask[index] = data.TASK_DATA[index];
                            }

                            //заказы
                            if (typeof data.ORDER_DATA != "undefined")
                                for(index in data.ORDER_DATA) {
                                    this_.dataorder[index] = data.ORDER_DATA[index];
                            }

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
                    savetask: function (index){
                        var cur = this;
                        var runner;

                        kabinet.loading();
                        runner = this.datarunner[index];

                        var form_data = new FormData();
                        for ( var key in runner ) {
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
                                kabinetStore.NotifyOk = '';
                                kabinetStore.NotifyOk = data.message;

                                cur.datarunner[index] = data.runner;
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

                                // сбрасываем данные до сохранения
                                setTimeout(()=>cur.resetSave(index),500);

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
                    isViewReport(status){
                        if ([7,8,9,10].indexOf(parseInt(status)) != -1) return true;

                        return false;
                    },
                    alertStyle(status){
                        if ([0].indexOf(parseInt(status)) != -1) return 'alert-planned';
                        if ([3,5,8].indexOf(parseInt(status)) != -1) return 'alert-user-attention';
                        if ([1,2,4,6,61,7].indexOf(parseInt(status)) != -1) return 'alert-worked';
                        if ([9].indexOf(parseInt(status)) != -1) return 'alert-done';
                        if ([10].indexOf(parseInt(status)) != -1) return 'alert-cancel';
                    },
                    catalogItem(PRODUCT_ID){
                        for(element of this.data3){
                            if (element.ID == PRODUCT_ID) return element;
                        }
                    },
                    getFileName(url) {
                        // Извлекаем имя файла из URL
                        return url.split('/').pop() || 'photo.jpg';
                    }
                },
                mounted() {
                    var cur = this;
                    this.$root.offset = 0;
                    if(parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;
                },
                components: {
                    mydatepicker,
                    mytypeahead,
                    sharephoto,
                    accountfield,
                    changestatus,
                    changeResponsible,
                    messangerperformances,
                    richtext,
                    SortableHeader, // Добавляем новый компонент
                    changenotes // Добавляем новый компонент
                },
                // language=Vue
                template: '#kabinet-content'
            });

            configureVueApp(adminClientListApplication);

        }
    }
}());