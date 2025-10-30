const taskinfoApplicationConfig = {
    data() {
        return {
            'TASK_ID': PHPPARAMS.TASK_ID,
            'taskData': {},
            'productData': {},
            'showAllPhotos': false,
            'photoLimit': 5,
            'originalTaskData': {}
        }
    },
    setup(){
        const {taskStatus_m,taskStatus_v,taskStatus_b} = task_status();

        return {
            taskStatus_m,
            taskStatus_v
        };
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
        ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
        ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
        ...BX.Vue3.Pinia.mapState(runnerlistStore, ['datarunner']),

        // Вычисляемые свойства для данных задачи
        currentTask() {
            return this.datatask.find(task => task.ID === this.TASK_ID) || {};
        },

        // Ограниченный список фото
        limitedPhotos() {
            if (!this.taskData.UF_PHOTO_ORIGINAL) return [];
            return this.showAllPhotos ?
                this.taskData.UF_PHOTO_ORIGINAL :
                this.taskData.UF_PHOTO_ORIGINAL.slice(0, this.photoLimit);
        },

        // Отфильтрованные опции для согласования (без пустого элемента)
        filteredCoordinationOptions() {
            if (!this.taskData.UF_COORDINATION_ORIGINAL) return [];
            return this.taskData.UF_COORDINATION_ORIGINAL.filter((option, index) => index > 0);
        },

        // Отфильтрованные опции для отчетности (без пустого элемента)
        filteredReportingOptions() {
            if (!this.taskData.UF_REPORTING_ORIGINAL) return [];
            return this.taskData.UF_REPORTING_ORIGINAL.filter((option, index) => index > 0);
        },

        // Проверка наличия изменений
        hasChanges() {
            if (!this.taskData || !this.originalTaskData) return false;

            // Сравниваем основные поля
            const fieldsToCompare = [
                'UF_JUSTFIELD',
                'UF_COORDINATION',
                'UF_REPORTING'
            ];

            for (let field of fieldsToCompare) {
                if (this.taskData[field] !== this.originalTaskData[field]) {
                    return true;
                }
            }

            // Сравниваем массив ссылок
            if (this.hasLinksChanged()) {
                return true;
            }

            return false;
        },

        // Текущие ссылки для отображения (только непустые)
        currentLinks() {
            if (!this.taskData.UF_TARGET_SITE) return [];
            return this.taskData.UF_TARGET_SITE.filter(link =>
                link.VALUE && link.VALUE.trim() !== ''
            );
        },

        // Проверка наличия ссылок для отображения
        hasLinks() {
            return this.currentLinks.length > 0;
        }
    },
    methods: {
        ...helperVueComponents(),
        ...addNewMethods(),
        // Глубокое копирование объекта
        deepCopy(obj) {
            if (obj === null || typeof obj !== 'object') return obj;
            if (obj instanceof Date) return new Date(obj);
            if (obj instanceof Array) return obj.map(item => this.deepCopy(item));
            if (obj instanceof Object) {
                const copiedObj = {};
                for (let key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        copiedObj[key] = this.deepCopy(obj[key]);
                    }
                }
                return copiedObj;
            }
        },

        // Инициализация данных задачи
        initializeTaskData() {
            // Используем глубокое копирование вместо spread оператора
            this.taskData = this.deepCopy(this.currentTask);
            this.originalTaskData = this.deepCopy(this.currentTask);

            // Инициализация productData
            this.initializeProductData();
        },

        // Инициализация данных продукта
        initializeProductData() {
            const project = this.data.find(p => p.ID === this.taskData.UF_PROJECT_ID);
            if (project && this.data2[project.UF_ORDER_ID]) {
                this.productData = this.data2[project.UF_ORDER_ID][this.taskData.UF_PRODUKT_ID] || {};
            }
        },

        // Проверка обязательного поля
        isRequiredField(task, fieldName) {
            const fieldValue = task[fieldName];

            if (fieldValue == null) return true;
            if (typeof fieldValue === 'string') return fieldValue.trim() === '';
            if (Array.isArray(fieldValue)) {
                if (fieldValue.length === 0) return true;
                return fieldValue.some(item =>
                    item && typeof item.VALUE !== 'undefined' && item.VALUE.toString().trim() === ''
                );
            }
            return false;
        },

        // Добавление новой ссылки
        addMoreLink() {
            if (!this.taskData.UF_TARGET_SITE) {
                this.taskData.UF_TARGET_SITE = [];
            }

            if (this.taskData.UF_TARGET_SITE.length > 4) {
                this.showNotification("Превышен лимит добавления ссылок", "error");
                return;
            }

            this.taskData.UF_TARGET_SITE.push({ VALUE: '' });
        },

        // Проверка изменений в ссылках
        hasLinksChanged() {
            const currentLinks = this.taskData.UF_TARGET_SITE || [];
            const originalLinks = this.originalTaskData.UF_TARGET_SITE || [];

            // Если разное количество ссылок
            if (currentLinks.length !== originalLinks.length) {
                return true;
            }

            // Проверяем каждую ссылку
            for (let i = 0; i < currentLinks.length; i++) {
                const currentLink = currentLinks[i]?.VALUE || '';
                const originalLink = originalLinks[i]?.VALUE || '';

                if (currentLink !== originalLink) {
                    return true;
                }
            }

            return false;
        },

        // Удаление фото
        removePhoto(photoId) {
            if (!this.taskData.UF_PHOTO_DELETE) {
                this.taskData.UF_PHOTO_DELETE = [];
            }
            this.taskData.UF_PHOTO_DELETE.push(photoId);
            this.saveTaskData();
        },

        // Обработчик изменения фото
        onPhotoChange(event) {
            this.taskData.UF_PHOTO = event.target.files;
            this.saveTaskData();
        },

        // Обработчик изменения input - ДОБАВЛЕН DEBOUNCE
        onInputChange() {
            // Дебаунс для избежания частых проверок
            if (this.inputTimeout) {
                clearTimeout(this.inputTimeout);
            }
            this.inputTimeout = setTimeout(() => {
                // Принудительное обновление computed свойства
                this.$forceUpdate();
            }, 300);
        },

        // Сохранение данных задачи
        saveTaskData() {
            const formData = this.dataToFormData(this.taskData);

            this.saveData('bitrix:kabinet.evn.taskevents.edittask', formData, (data) => {
                // Обновляем данные задачи после сохранения
                const taskStore = tasklistStore();
                taskStore.datatask = data.task;

                // Обновляем локальные данные
                this.initializeTaskData();

                this.showNotification("Данные успешно сохранены", "success");
            });
        },

        // Вспомогательный метод для показа уведомлений
        showNotification(message, type = "info") {
            const kabinetStore = usekabinetStore();
            if (type === "success") {
                kabinetStore.NotifyOk = message;
            } else {
                kabinetStore.Notify = message;
            }
        },

        /*
        taskQueueCount(task_id){
            const calendarStore_ = calendarStore();
            const taskEvents = calendarStore_.getEventsByTaskId(task_id);
            return taskEvents.length;
        }
        */

        taskQueueCount(task_id){
            const { taskStatus_v } = task_status();
            const counts = taskStatus_v(task_id);

            // Суммируем все активные события (запланированные, работающие и требующие внимания)
            return counts.stopwark + counts.work + counts.alert;
        },

        // Копирование ссылки в буфер обмена
        copyLinkToClipboard(link, index) {
            if (!link || link.trim() === '') return;

            navigator.clipboard.writeText(link.trim())
                .then(() => {
                    // Показываем уведомление об успешном копировании
                  //  this.showNotification(`Ссылка #${index + 1} скопирована в буфер обмена`, "success");
                })
                .catch(err => {
                    console.error('Ошибка при копировании: ', err);
                    // Fallback для старых браузеров
                    this.fallbackCopyToClipboard(link.trim());
                });
        },

        // Fallback метод для копирования
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
              //  this.showNotification("Ссылка скопирована в буфер обмена", "success");
            } catch (err) {
                console.error('Ошибка при копировании (fallback): ', err);
                this.showNotification("Не удалось скопировать ссылку", "error");
            }
            document.body.removeChild(textArea);
        },
    },
    watch: {
        // Следим за изменениями в хранилище задач
        datatask: {
            handler() {
                this.initializeTaskData();
            },
            deep: true
        },

        // Следим за изменениями в taskData
        taskData: {
            handler() {
                // Принудительное обновление computed свойства при изменении taskData
                this.$forceUpdate();
            },
            deep: true
        }
    },
    mounted() {
        this.initializeTaskData();
    },
    template: '#task-info-template'
};