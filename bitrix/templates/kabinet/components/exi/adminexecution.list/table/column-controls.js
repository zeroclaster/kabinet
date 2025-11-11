class ColumnManager {
    constructor(hotTable, fieldLabels, editableFields) {
        this.hotTable = hotTable;
        this.fieldLabels = fieldLabels;
        this.editableFields = editableFields;
        this.COLUMN_SETTINGS_COOKIE = 'handsontable_columns_visibility';

        this.init();
    }

    init() {
        this.bindEvents();
        this.applySavedColumnSettings();
    }

    // Функции для работы с cookies
    setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    deleteCookie(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
    }

    // Функция для сохранения настроек колонок
    saveColumnSettings() {
        var settings = {};
        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            var columnKey = checkbox.getAttribute('data-column');
            settings[columnKey] = checkbox.checked;
        });
        this.setCookie(this.COLUMN_SETTINGS_COOKIE, JSON.stringify(settings), 30);
    }

    // Функция для загрузки настроек колонок
    loadColumnSettings() {
        var savedSettings = this.getCookie(this.COLUMN_SETTINGS_COOKIE);
        if (savedSettings) {
            try {
                return JSON.parse(savedSettings);
            } catch (e) {
                console.error('Ошибка при загрузке настроек колонок:', e);
                return null;
            }
        }
        return null;
    }

    // Функция для применения сохраненных настроек
    applySavedColumnSettings() {
        var savedSettings = this.loadColumnSettings();
        console.log(savedSettings);
        if (savedSettings) {
            document.querySelectorAll('.column-toggle').forEach((checkbox) => {
                var columnKey = checkbox.getAttribute('data-column');
                if (savedSettings.hasOwnProperty(columnKey)) {
                    checkbox.checked = savedSettings[columnKey];
                }
            });
            this.applyColumnVisibility();
        } else {
            // Все колонки видны по умолчанию (кроме id, который мы исключим на уровне создания колонок)
            document.querySelectorAll('.column-toggle').forEach((checkbox) => {
                checkbox.checked = true;
            });
            this.applyColumnVisibility();
        }
    }

    // Привязка событий
    bindEvents() {
        const toggleButton = document.getElementById('toggleColumnMenu');
        const columnMenu = document.getElementById('columnMenu');
        const closeButton = document.getElementById('closeColumnMenu');
        const selectAllButton = document.getElementById('selectAllColumns');
        const deselectAllButton = document.getElementById('deselectAllColumns');
        const resetButton = document.getElementById('resetColumnSettings');

        // Переключение меню колонок
        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                if (columnMenu.style.display === 'none') {
                    columnMenu.style.display = 'block';
                    this.updateColumnCheckboxes();
                } else {
                    columnMenu.style.display = 'none';
                }
            });
        }

        // Закрытие меню
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                columnMenu.style.display = 'none';
            });
        }

        // Выбрать все колонки
        if (selectAllButton) {
            selectAllButton.addEventListener('click', () => {
                this.selectAllColumns();
            });
        }

        // Снять все колонки
        if (deselectAllButton) {
            deselectAllButton.addEventListener('click', () => {
                this.deselectAllColumns();
            });
        }

        // Сброс настроек
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                this.resetColumnSettings();
            });
        }

        // Обработка изменений чекбоксов
        document.querySelectorAll('.column-toggle').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                this.applyColumnVisibility();
                this.saveColumnSettings();
            });
        });

        // Закрытие меню при клике вне его
        document.addEventListener('click', (event) => {
            if (columnMenu && !columnMenu.contains(event.target) && event.target !== toggleButton) {
                columnMenu.style.display = 'none';
            }
        });
    }

    // Выбрать все колонки
    selectAllColumns() {
        document.querySelectorAll('.column-toggle').forEach((checkbox) => {
            checkbox.checked = true;
        });
        this.applyColumnVisibility();
        this.saveColumnSettings();
    }

    // Снять все колонки
    deselectAllColumns() {
        document.querySelectorAll('.column-toggle').forEach((checkbox) => {
            checkbox.checked = false;
        });
        this.applyColumnVisibility();
        this.saveColumnSettings();
    }

    // Сброс настроек колонок
    resetColumnSettings() {
        if (confirm('Вы уверены, что хотите сбросить настройки колонок к значениям по умолчанию?')) {
            this.deleteCookie(this.COLUMN_SETTINGS_COOKIE);
            document.querySelectorAll('.column-toggle').forEach((checkbox) => {
                checkbox.checked = true;
            });
            this.applyColumnVisibility();
        }
    }

    // Функция обновления состояния чекбоксов на основе видимых колонок
    updateColumnCheckboxes() {
        if (!this.hotTable) return;

        const currentColumns = this.hotTable.getSettings().columns;
        const visibleColumns = currentColumns.map((column) => {
            return column.data;
        });

        document.querySelectorAll('.column-toggle').forEach((checkbox) => {
            const columnKey = checkbox.getAttribute('data-column');
            checkbox.checked = visibleColumns.includes(columnKey);
        });
    }

    // Функция применения видимости колонок
    applyColumnVisibility() {
        if (!this.hotTable) return;

        const columnsToShow = [];

        // Собираем колонки для отображения (исключаем поле id)
        document.querySelectorAll('.column-toggle').forEach((checkbox) => {
            const columnKey = checkbox.getAttribute('data-column');
            if (checkbox.checked && columnKey !== 'id') { // Всегда исключаем поле id
                columnsToShow.push(columnKey);
            }
        });

        // Сохраняем текущие данные
        const currentData = this.hotTable.getSourceData();

        // Создаем новые настройки колонок
        const newColumns = columnsToShow.map((key) => {
            return this.getColumnConfig(key);
        });

        // Обновляем настройки таблицы с явной передачей данных
        this.hotTable.updateSettings({
            columns: newColumns,
            data: currentData
        });
    }

    // Вспомогательная функция для получения конфигурации колонки
    getColumnConfig(key) {
        // Используем общую функцию из column-configs.js
        return window.columnConfigs.getColumnConfig(key, this.fieldLabels, this.editableFields);
    }
}

// Глобальная переменная для доступа к менеджеру колонок
window.columnManager = null;