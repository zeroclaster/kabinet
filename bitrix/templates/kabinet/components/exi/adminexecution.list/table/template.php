<?php
// template.php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');

// Массив с русскими названиями полей для отображения
$fieldLabels = [
    'id' => 'ID',
    'planned_date' => 'Плановая дата выполнения',
    'client' => 'Клиент',
    'project' => 'Проект',
    'task' => 'Задача',
    'created_date' => 'Дата создания',
    'completion_date' => 'Дата завершения',
    'coordination' => 'Согласование',
    'reporting' => 'Отчетность',
    'process_type' => 'Тип процесса',
    'link' => 'Ссылка',
    'photo' => 'Фото',
    'review_text' => 'Текст отзыва',
    'status' => 'Статус',
    'responsible' => 'Ответственный',
    'publication_date' => 'Дата публикации',
    'account_name' => 'Имя аккаунта',
    'login' => 'Логин',
    'password' => 'Пароль',
    'ip_address' => 'IP размещения'
];

// Поля, которые можно редактировать
$editableFields = [
    'planned_date',
    'review_text',
    'responsible',
    'publication_date',
    'account_name',
    'login',
    'password',
    'ip_address'
];

// Подготавливаем данные для JavaScript
$executionsData = prepareExecutionsData($arResult, $arParams, $runnerManager);
?>

<!-- Подключаем CSS Handsontable -->
<link href="https://cdn.jsdelivr.net/npm/handsontable@14.0.0/dist/handsontable.full.min.css" rel="stylesheet">

<div id="kabinetcontent">
    <div class="controls mb-3">
        <div class="alert alert-info">
            <small>Редактируемые поля: Плановая дата выполнения, Текст отзыва, Ответственный, Дата публикации, Имя аккаунта, Логин, Пароль, IP размещения</small>
        </div>
        <div class="column-controls mt-2">
            <button id="toggleColumnMenu" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-columns"></i> Управление колонками
            </button>
            <div id="columnMenu" class="column-menu" style="display: none;">
                <div class="column-menu-header">
                    <h6>Видимость колонок</h6>
                    <button type="button" class="btn-close" id="closeColumnMenu"></button>
                </div>
                <div class="column-menu-body">
                    <?php foreach ($fieldLabels as $key => $label): ?>
                        <div class="form-check">
                            <input class="form-check-input column-toggle" type="checkbox"
                                   id="column-<?= $key ?>" data-column="<?= $key ?>" checked>
                            <label class="form-check-label" for="column-<?= $key ?>">
                                <?= $label ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="column-menu-footer">
                    <button id="selectAllColumns" class="btn btn-sm btn-outline-secondary">Выбрать все</button>
                    <button id="deselectAllColumns" class="btn btn-sm btn-outline-secondary">Снять все</button>
                    <button id="resetColumnSettings" class="btn btn-sm btn-outline-warning">Сбросить настройки</button>
                </div>
            </div>
        </div>
    </div>
    <div id="handsontable-container" style="height: 600px; overflow: hidden;"></div>
</div>

<!-- Подключаем JavaScript Handsontable -->
<script src="https://cdn.jsdelivr.net/npm/handsontable@14.0.0/dist/handsontable.full.min.js"></script>

<script>
    // Создаем JavaScript массив с данными исполнений
    var executionsArray = <?= CUtil::PhpToJSObject($executionsData, false, true) ?>;

    // Создаем JavaScript объект с русскими названиями полей
    var fieldLabels = <?= CUtil::PhpToJSObject($fieldLabels, false, true) ?>;

    // Поля, которые можно редактировать
    var editableFields = <?= CUtil::PhpToJSObject($editableFields, false, true) ?>;

    // Глобальные переменные
    window.executionsData = executionsArray;
    window.executionsFieldLabels = fieldLabels;
    window.hotTable = null;

    // Ключ для хранения настроек в cookies
    var COLUMN_SETTINGS_COOKIE = 'handsontable_columns_visibility';

    // Регистрируем русский язык для Handsontable
    Handsontable.languages.registerLanguageDictionary({
        languageCode: 'ru-RU',
        // Минимальный набор переводов для русского языка
        labels: {
            'rowHeaders': 'Строки',
            'colHeaders': 'Колонки',
            'filter': 'Фильтр',
            'clearColumnFilter': 'Очистить фильтр',
            'sortAscending': 'Сортировать по возрастанию',
            'sortDescending': 'Сортировать по убыванию',
            'undo': 'Отменить',
            'redo': 'Повторить',
            'copy': 'Копировать',
            'cut': 'Вырезать',
            'paste': 'Вставить',
            'search': 'Поиск',
            'noResults': 'Результатов не найдено',
            'alignment': 'Выравнивание',
            'left': 'По левому краю',
            'center': 'По центру',
            'right': 'По правому краю',
            'justify': 'По ширине',
            'freezeColumn': 'Закрепить колонку',
            'unfreezeColumn': 'Открепить колонку',
            'insertRowAbove': 'Вставить строку выше',
            'insertRowBelow': 'Вставить строку ниже',
            'removeRow': 'Удалить строку',
            'insertColumnBefore': 'Вставить колонку слева',
            'insertColumnAfter': 'Вставить колонку справа',
            'removeColumn': 'Удалить колонку',
            'borders': 'Границы',
            'allBorders': 'Все границы',
            'noBorders': 'Без границ'
        },
        // Добавляем переводы для календаря
        date: {
            // Дни недели
            m: ['Пн', 'Понедельник'],
            t: ['Вт', 'Вторник'],
            w: ['Ср', 'Среда'],
            th: ['Чт', 'Четверг'],
            fr: ['Пт', 'Пятница'],
            s: ['Сб', 'Суббота'],
            su: ['Вс', 'Воскресенье'],
            // Месяцы
            jan: ['Янв', 'Январь'],
            feb: ['Фев', 'Февраль'],
            mar: ['Мар', 'Март'],
            apr: ['Апр', 'Апрель'],
            may: ['Май', 'Май'],
            jun: ['Июн', 'Июнь'],
            jul: ['Июл', 'Июль'],
            aug: ['Авг', 'Август'],
            sep: ['Сен', 'Сентябрь'],
            oct: ['Окт', 'Октябрь'],
            nov: ['Ноя', 'Ноябрь'],
            dec: ['Дек', 'Декабрь'],
            am: 'AM',
            pm: 'PM'
        }
    });

    // Функции для работы с cookies
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function deleteCookie(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
    }

    // Функция для сохранения настроек колонок
    function saveColumnSettings() {
        var settings = {};
        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            var columnKey = checkbox.getAttribute('data-column');
            settings[columnKey] = checkbox.checked;
        });
        setCookie(COLUMN_SETTINGS_COOKIE, JSON.stringify(settings), 30); // Сохраняем на 30 дней
    }

    // Функция для загрузки настроек колонок
    function loadColumnSettings() {
        var savedSettings = getCookie(COLUMN_SETTINGS_COOKIE);
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
    function applySavedColumnSettings() {
        var savedSettings = loadColumnSettings();
        if (savedSettings) {
            document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
                var columnKey = checkbox.getAttribute('data-column');
                if (savedSettings.hasOwnProperty(columnKey)) {
                    checkbox.checked = savedSettings[columnKey];
                }
            });
            applyColumnVisibility();
        }
    }

    // Инициализация Handsontable
    document.addEventListener('DOMContentLoaded', function() {
        var container = document.getElementById('handsontable-container');

        if (executionsArray.length > 0) {
            initializeTable();
            initializeColumnControls();
            // Применяем сохраненные настройки после инициализации
            setTimeout(function() {
                applySavedColumnSettings();
            }, 100);
        } else {
            container.innerHTML = '<div class="alert alert-info">Нет данных об исполнениях для отображения</div>';
        }
    });

    // Функция инициализации таблицы
    function initializeTable() {
        var container = document.getElementById('handsontable-container');

        // Подготавливаем колонки для Handsontable
        var columns = Object.keys(fieldLabels).map(function(key) {
            var columnConfig = {
                data: key,
                title: fieldLabels[key],
                width: 150,
                readOnly: !editableFields.includes(key) // Только указанные поля можно редактировать
            };

            // Настройки для specific полей
            switch(key) {
                case 'id':
                    columnConfig.width = 80;
                    columnConfig.type = 'numeric';
                    break;
                case 'planned_date':
                case 'created_date':
                case 'completion_date':
                case 'publication_date':
                    columnConfig.width = 120;
                    columnConfig.type = 'date'; // Изменяем тип на 'date'
                    columnConfig.dateFormat = 'DD.MM.YYYY'; // Формат даты
                    columnConfig.correctFormat = true;
                    columnConfig.defaultDate = new Date().toISOString().split('T')[0]; // Сегодняшняя дата по умолчанию
                    // Подсветка редактируемых полей дат
                    if (key === 'planned_date' || key === 'publication_date') {
                        columnConfig.className = 'editable-cell';
                    }
                    break;
                case 'client':
                case 'project':
                    columnConfig.width = 200;
                    break;
                case 'task':
                    columnConfig.width = 250;
                    break;
                case 'review_text':
                    columnConfig.width = 300;
                    columnConfig.className = 'editable-cell';
                    break;
                case 'photo':
                    columnConfig.width = 200;
                    columnConfig.renderer = function(instance, td, row, col, prop, value) {
                        if (value) {
                            // Отображаем просто текст (ссылку на файл), не кликабельную
                            td.textContent = value;
                            td.title = value; // Добавляем подсказку с полным текстом
                        } else {
                            td.textContent = '-';
                        }
                        return td;
                    };
                    break;
                case 'link':
                    columnConfig.width = 200;
                    columnConfig.renderer = function(instance, td, row, col, prop, value) {
                        if (value) {
                            td.innerHTML = '<a href="' + value + '" target="_blank" title="' + value + '">' +
                                (value.length > 30 ? value.substring(0, 30) + '...' : value) +
                                '</a>';
                        } else {
                            td.textContent = '-';
                        }
                        return td;
                    };
                    break;
                case 'responsible':
                case 'account_name':
                case 'login':
                case 'password':
                case 'ip_address':
                    columnConfig.className = 'editable-cell';
                    break;
            }

            return columnConfig;
        });

        // Создаем Handsontable с Excel-подобным стилем
        window.hotTable = new Handsontable(container, {
            data: executionsArray,
            columns: columns,
            rowHeaders: true,
            colHeaders: true,
            columnSorting: true,
            filters: true,
            dropdownMenu: true,
            contextMenu: true,
            manualColumnResize: true,
            manualRowResize: true,
            manualColumnMove: true, // Включаем перетаскивание колонок
            licenseKey: 'non-commercial-and-evaluation',
            height: 550,
            width: '100%',
            wordWrap: false,
            autoWrapRow: true,
            autoWrapCol: true,
            language: 'ru-RU', // Теперь язык зарегистрирован
            search: true,
            stretchH: 'all',
            customBorders: true,
            // Стиль как в Excel
            currentRowClassName: 'current-row',
            currentColClassName: 'current-col',
            // Настройки для Excel-подобного поведения
            enterMoves: {row: 0, col: 1},
            tabMoves: {row: 0, col: 1},
            autoColumnSize: {
                samplingRatio: 23
            },
            // Обработка изменений
            afterChange: function(changes, source) {
                if (source === 'loadData') {
                    return; // Игнорируем изменения при загрузке данных
                }

                if (changes) {
                    changes.forEach(function(change) {
                        var row = change[0];
                        var field = change[1];
                        var oldValue = change[2];
                        var newValue = change[3];

                        console.log('Изменение:', {
                            row: row,
                            field: field,
                            oldValue: oldValue,
                            newValue: newValue,
                            executionId: executionsArray[row].id
                        });

                        // Здесь можно добавить логику сохранения изменений на сервер
                        // saveChanges(executionsArray[row].id, field, newValue);
                    });
                }
            },
            // Настройки выделения
            selectionMode: 'range',
            fillHandle: {
                direction: 'vertical',
                autoInsertRow: false
            }
        });
    }

    // Функция инициализации управления колонками
    function initializeColumnControls() {
        var toggleButton = document.getElementById('toggleColumnMenu');
        var columnMenu = document.getElementById('columnMenu');
        var closeButton = document.getElementById('closeColumnMenu');
        var selectAllButton = document.getElementById('selectAllColumns');
        var deselectAllButton = document.getElementById('deselectAllColumns');

        // Переключение меню колонок
        toggleButton.addEventListener('click', function() {
            if (columnMenu.style.display === 'none') {
                columnMenu.style.display = 'block';
                updateColumnCheckboxes();
            } else {
                columnMenu.style.display = 'none';
            }
        });

        // Закрытие меню
        closeButton.addEventListener('click', function() {
            columnMenu.style.display = 'none';
        });

        // Выбрать все колонки
        selectAllButton.addEventListener('click', function() {
            document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
                checkbox.checked = true;
            });
            applyColumnVisibility();
            saveColumnSettings(); // Сохраняем настройки
        });

        // Снять все колонки
        deselectAllButton.addEventListener('click', function() {
            document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
                checkbox.checked = false;
            });
            applyColumnVisibility();
            saveColumnSettings(); // Сохраняем настройки
        });

        // Обработка изменений чекбоксов
        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                applyColumnVisibility();
                saveColumnSettings(); // Сохраняем настройки при каждом изменении
            });
        });

        // Закрытие меню при клике вне его
        document.addEventListener('click', function(event) {
            if (!columnMenu.contains(event.target) && event.target !== toggleButton) {
                columnMenu.style.display = 'none';
            }
        });

        // В функции initializeColumnControls() добавьте:
        var resetButton = document.getElementById('resetColumnSettings');
        resetButton.addEventListener('click', function() {
            if (confirm('Вы уверены, что хотите сбросить настройки колонок к значениям по умолчанию?')) {
                resetColumnSettings();
            }
        });

        // Изначально все колонки включены (если нет сохраненных настроек)
        var savedSettings = loadColumnSettings();
        if (!savedSettings) {
            document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        }
    }

    // Функция обновления состояния чекбоксов на основе видимых колонок
    function updateColumnCheckboxes() {
        if (!window.hotTable) return;

        var currentColumns = window.hotTable.getSettings().columns;
        var visibleColumns = currentColumns.map(function(column) {
            return column.data;
        });

        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            var columnKey = checkbox.getAttribute('data-column');
            checkbox.checked = visibleColumns.includes(columnKey);
        });
    }

    // Функция применения видимости колонок
    function applyColumnVisibility() {
        if (!window.hotTable) return;

        var columnsToShow = [];

        // Собираем колонки для отображения
        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            var columnKey = checkbox.getAttribute('data-column');
            if (checkbox.checked) {
                columnsToShow.push(columnKey);
            }
        });

        // Сохраняем текущие данные
        var currentData = window.hotTable.getSourceData();

        // Создаем новые настройки колонок
        var newColumns = columnsToShow.map(function(key) {
            return getColumnConfig(key);
        });

        // Обновляем настройки таблицы с явной передачей данных
        window.hotTable.updateSettings({
            columns: newColumns,
            data: currentData // Явно передаем данные
        });
    }

    // Вспомогательная функция для получения конфигурации колонки
    function getColumnConfig(key) {
        var columnConfig = {
            data: key,
            title: fieldLabels[key],
            width: 150,
            readOnly: !editableFields.includes(key)
        };

        // Настройки для specific полей
        switch(key) {
            case 'id':
                columnConfig.width = 80;
                columnConfig.type = 'numeric';
                break;
            case 'planned_date':
            case 'created_date':
            case 'completion_date':
            case 'publication_date':
                columnConfig.width = 120;
                columnConfig.type = 'date'; // Изменяем тип на 'date'
                columnConfig.dateFormat = 'DD.MM.YYYY'; // Формат даты
                columnConfig.correctFormat = true;
                columnConfig.defaultDate = new Date().toISOString().split('T')[0];
                if (key === 'planned_date' || key === 'publication_date') {
                    columnConfig.className = 'editable-cell';
                }
                break;
            case 'client':
            case 'project':
                columnConfig.width = 200;
                break;
            case 'task':
                columnConfig.width = 250;
                break;
            case 'review_text':
                columnConfig.width = 300;
                columnConfig.className = 'editable-cell';
                break;
            case 'photo':
                columnConfig.width = 200;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        // Отображаем просто текст (ссылку на файл), не кликабельную
                        td.textContent = value;
                        td.title = value; // Добавляем подсказку с полным текстом
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'link':
                columnConfig.width = 200;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        td.innerHTML = '<a href="' + value + '" target="_blank" title="' + value + '">' +
                            (value.length > 30 ? value.substring(0, 30) + '...' : value) +
                            '</a>';
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'responsible':
            case 'account_name':
            case 'login':
            case 'password':
            case 'ip_address':
                columnConfig.className = 'editable-cell';
                break;
        }

        return columnConfig;
    }

    // Функция для обновления данных таблицы
    function updateTableData(newData) {
        if (window.hotTable && newData) {
            window.hotTable.updateData(newData);
        }
    }

    // Функция для добавления новых данных
    function addTableData(newData) {
        if (window.hotTable && newData) {
            var currentData = window.hotTable.getData();
            var updatedData = currentData.concat(newData);
            window.hotTable.updateData(updatedData);
        }
    }

    // Функция для сброса настроек (опционально, можно добавить кнопку)
    function resetColumnSettings() {
        deleteCookie(COLUMN_SETTINGS_COOKIE);
        document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
            checkbox.checked = true;
        });
        applyColumnVisibility();
    }
</script>