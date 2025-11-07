class TableSaveManager {
    constructor(hotTable, executionsArray, editableFields) {
        this.hotTable = hotTable;
        this.executionsArray = executionsArray;
        this.editableFields = editableFields;
        this.saveTimeouts = {};
        this.SAVE_DELAY = 1000; // 3 секунды задержки

        this.init();
    }

    init() {
        console.log('TableSaveManager инициализирован');
    }

    // Обработчик изменения ячейки
    handleCellChange(row, field, oldValue, newValue) {
        // Проверяем, можно ли редактировать это поле
        if (!this.isFieldEditable(field)) {
            console.log('Поле не редактируемое:', field);
            return;
        }

        // Проверяем, действительно ли значение изменилось
        if (oldValue === newValue) {
            return;
        }

        const executionId = this.getExecutionId(row);
        if (!executionId) {
            console.error('Не удалось получить ID исполнения для строки:', row);
            return;
        }

        console.log('Изменение редактируемого поля:', {
            executionId: executionId,
            field: field,
            oldValue: oldValue,
            newValue: newValue,
            row: row
        });

        // Создаем уникальный ключ для таймера
        const timeoutKey = `${executionId}_${field}`;

        // Очищаем предыдущий таймер для этого поля
        if (this.saveTimeouts[timeoutKey]) {
            clearTimeout(this.saveTimeouts[timeoutKey]);
        }

        // Устанавливаем новый таймер
        this.saveTimeouts[timeoutKey] = setTimeout(() => {
            this.saveFieldChange(executionId, field, newValue, row);
            delete this.saveTimeouts[timeoutKey];
        }, this.SAVE_DELAY);

        // Показываем индикатор сохранения
        this.showSavingIndicator(row, field);
    }

    // Проверка, можно ли редактировать поле
    isFieldEditable(field) {
        return this.editableFields.includes(field);
    }

    // Получение ID исполнения по номеру строки
    getExecutionId(row) {
        if (this.executionsArray && this.executionsArray[row]) {
            return this.executionsArray[row].id;
        }
        return null;
    }

    // Показать индикатор сохранения
    showSavingIndicator(row, field) {
        const cell = this.hotTable.getCell(row, this.getColumnIndex(field));
        if (cell) {
            cell.style.backgroundColor = '#fff3cd'; // Желтый фон
            cell.title = 'Сохранение...';
        }
    }

    // Скрыть индикатор сохранения
    hideSavingIndicator(row, field) {
        const cell = this.hotTable.getCell(row, this.getColumnIndex(field));
        if (cell) {
            cell.style.backgroundColor = '';
            cell.title = '';
        }
    }

    // Получить индекс колонки по имени поля
    getColumnIndex(field) {
        const settings = this.hotTable.getSettings();
        if (settings && settings.columns) {
            for (let i = 0; i < settings.columns.length; i++) {
                if (settings.columns[i].data === field) {
                    return i;
                }
            }
        }
        return -1;
    }

    // Сохранение изменения поля
    saveFieldChange(executionId, field, value, row) {
        console.log('Сохранение поля:', { executionId, field, value });

        // Подготавливаем данные для отправки
        const formData = new FormData();
        formData.append('ID', executionId);

        // Преобразуем поле в формат UF_ если нужно
        const fieldName = this.mapFieldToUF(field);
        formData.append(fieldName, value);

        // Показываем индикатор загрузки
        this.showSavingIndicator(row, field);

        // Отправляем запрос на сервер
        BX.ajax.runAction('bitrix:kabinet.evn.runnerevents.edittable', {
            data: formData,
            getParameters: { usr: window.usr_id_const || 0 }
        })
            .then((response) => {
                console.log('Поле успешно сохранено:', response);
                this.hideSavingIndicator(row, field);

                // Показываем уведомление об успехе
                this.showNotification('Изменения сохранены', 'success');

                // Обновляем данные в массиве executionsArray
                this.updateLocalData(executionId, field, value);
            })
            .catch((response) => {
                console.error('Ошибка сохранения:', response);
                this.hideSavingIndicator(row, field);

                // Показываем ошибку
                this.showNotification('Ошибка сохранения: ' + (response.errors[0]?.message || 'Неизвестная ошибка'), 'error');

                // Восстанавливаем старое значение
                this.restoreOldValue(row, field);
            });
    }

    // Преобразование имени поля в формат UF_
    mapFieldToUF(field) {
        const fieldMap = {
            'planned_date': 'UF_PLANNE_DATE',
            'publication_date': 'UF_ACTUAL_DATE',
            'review_text': 'UF_REVIEW_TEXT',
            'responsible': 'UF_RESPONSIBLE',
            'account_name': 'UF_SITE_SETUP_ACCOUNT',
            'login': 'UF_SITE_SETUP_LOGIN',
            'password': 'UF_SITE_SETUP_PASS',
            'ip_address': 'UF_SITE_SETUP_IP',
            'UF_REPORT_LINK': 'UF_REPORT_LINK',
            'UF_REPORT_SCREEN': 'UF_REPORT_SCREEN',
            'UF_REPORT_FILE': 'UF_REPORT_FILE',
            'UF_REPORT_TEXT': 'UF_REPORT_TEXT'
        };

        return fieldMap[field] || field;
    }

    // Обновление локальных данных
    updateLocalData(executionId, field, value) {
        const execution = this.executionsArray.find(item => item.id == executionId);
        if (execution) {
            execution[field] = value;
        }
    }

    // Восстановление старого значения при ошибке
    restoreOldValue(row, field) {
        const execution = this.executionsArray[row];
        if (execution) {
            const oldValue = execution[field];
            this.hotTable.setDataAtCell(row, this.getColumnIndex(field), oldValue);
        }
    }

    // Показать уведомление
    showNotification(message, type = 'info') {
        // Используем существующую систему уведомлений или создаем простую
        const kabinetStore = usekabinetStore();
        if (kabinetStore) {
            kabinetStore.NotifyOk = "";
            if (type === 'success') {
                kabinetStore.NotifyOk = message;
            } else {
                kabinetStore.Notify = message;
            }
        } else {
            // Простое уведомление
            alert(message);
        }
    }

    // Очистка всех таймеров при уничтожении
    destroy() {
        for (const key in this.saveTimeouts) {
            clearTimeout(this.saveTimeouts[key]);
        }
        this.saveTimeouts = {};
    }
}

// Глобальная переменная для доступа к менеджеру сохранения
window.tableSaveManager = null;