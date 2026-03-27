const changenotes = BX.Vue3.BitrixVue.mutableComponent('change-notes', {
    template: `
        <div class="mb-3 form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0" :for="'notes-execution'+id_input">
                    {{ labelText }}
                </label>
                <button 
                    v-if="!isEditing && notesFulfiList.length > 0" 
                    class="btn btn-outline-secondary btn-sm"
                    @click="startEditing"
                    :title="editButtonTitle"
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
                                        v-if="!isEditing || editingNoteIndex !== index"
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
                                <!-- Отображаем информацию о редактировании если есть -->
                                <div v-if="note.edited_date" class="note-edit-meta text-muted small">
                                    <i class="fa fa-pencil-square-o"></i>
                                    Изменено: {{ formatDate(note.edited_date) }} 
                                    {{ note.edited_by ? 'пользователем ' + note.edited_by : '' }}
                                </div>
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
                            <i class="fa fa-info-circle"></i>
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
            editingNoteIndex: null,
            notesFulfiList: []
        }
    },
    props: {
        fulfillmentId: {
            type: [Number, String],
            default: null
        },
        objectclientId: {
            type: [Number, String],
            default: null
        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),

        // Определяем тип сущности и текст метки
        entityType() {
            if (this.fulfillmentId) return 'fulfillment';
            if (this.objectclientId) return 'client';
            return null;
        },

        entityId() {
            return this.fulfillmentId || this.objectclientId || null;
        },

        labelText() {
            if (this.entityType === 'client') return 'Заметки по клиенту';
            return 'Заметки';
        },

        editButtonTitle() {
            if (this.entityType === 'client') return 'Редактировать заметки клиента';
            return 'Редактировать заметку';
        }
    },
    mounted() {
        this.loadCurrentNote();
    },
    methods: {
        loadCurrentNote() {
            if (!this.entityId) return;

            const this_ = this;
            const action = this.entityType === 'client'
                ? 'bitrix:kabinet.evn.runnerevents.getclientnote'
                : 'bitrix:kabinet.evn.runnerevents.getcurrentnote';

            const data = this.entityType === 'client'
                ? { client_id: this.objectclientId }
                : { fulfillment_id: this.fulfillmentId };

            BX.ajax.runAction(action, { data })
                .then(function(response) {
                    if (response.data && response.data.note) {
                        try {
                            const parsedData = parseJSON(response.data.note);
                            if (Array.isArray(parsedData)) {
                                this_.notesFulfiList = parsedData;
                            } else if (parsedData && typeof parsedData === 'object') {
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
                })
                .catch(function(error) {
                    console.error('Error loading notes:', error);
                    this_.notesFulfiList = [];
                });
        },

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
            this.editingNoteIndex = null;
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

        deleteNote() {
            if (this.editingNoteIndex === null) return;

            if (!confirm('Вы уверены, что хотите удалить эту заметку?')) return;

            const updatedNotesList = this.notesFulfiList.filter((_, index) => index !== this.editingNoteIndex);
            this.saveNotesToServer(updatedNotesList, 'удалена');
        },

        saveNote() {
            if (!this.noteText.trim()) return;

            let updatedNotesList;
            let actionType;

            if (this.editingNoteIndex !== null) {
                // Редактирование существующей заметки
                updatedNotesList = [...this.notesFulfiList];
                updatedNotesList[this.editingNoteIndex] = {
                    ...updatedNotesList[this.editingNoteIndex],
                    text: this.noteText.trim(),
                    edited_date: new Date().toISOString(),
                    edited_by: this.datauser.PRINT_NAME || 'ADMIN'
                };
                actionType = 'обновлена';
            } else {
                // Создаем новую заметку
                const newNote = {
                    username: this.datauser.PRINT_NAME || 'ADMIN',
                    date: new Date().toISOString(),
                    text: this.noteText.trim()
                };
                updatedNotesList = [...this.notesFulfiList, newNote];
                actionType = 'сохранена';
            }

            this.saveNotesToServer(updatedNotesList, actionType);
        },

        saveNotesToServer(updatedNotesList, actionType = 'сохранена') {
            const this_ = this;
            const kabinetStore = usekabinetStore();
            const notesJsonString = JSON.stringify(updatedNotesList);

            const action = this.entityType === 'client'
                ? 'bitrix:kabinet.evn.runnerevents.saveclientnote'
                : 'bitrix:kabinet.evn.runnerevents.savenote';

            const data = this.entityType === 'client'
                ? {
                    client_id: this.objectclientId,
                    note_text: notesJsonString
                }
                : {
                    fulfillment_id: this.fulfillmentId,
                    note_text: notesJsonString
                };

            BX.ajax.runAction(action, { data })
                .then(function(response) {
                    if (response.data.success) {
                        kabinetStore.NotifyOk = '';
                        kabinetStore.NotifyOk = `Заметка ${actionType}`;
                        this_.notesFulfiList = updatedNotesList;
                        this_.noteText = '';
                        this_.isEditing = false;
                        this_.editingNoteIndex = null;
                    } else {
                        kabinetStore.Notify = response.data.message || `Ошибка при ${actionType === 'удалена' ? 'удалении' : 'сохранении'} заметки`;
                    }
                })
                .catch(function(response) {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = `Ошибка при ${actionType === 'удалена' ? 'удалении' : 'сохранении'} заметки`;
                    console.error('Save note error:', response);
                });
        },

        formatDate(dateString) {
            if (!dateString) return '';
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