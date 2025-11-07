<?php
use Bitrix\Main\Page\Asset;
// template.php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');

// Подключаем настройки полей
include __DIR__ . '/field-settings.php';

// Подготавливаем данные для JavaScript
$executionsData = prepareExecutionsData($arResult, $arParams, $runnerManager);

// Добавьте для отладки:
//var_dump("Количество исполнений: " . count($executionsData));
foreach ($executionsData as $index => $execution) {
    //var_dump("Исполнение {$index}: статус = '" . $execution['status'] . "'");
}


Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/assets/js/handsontable/handsontable.full.min.css");
?>

<div id="kabinetcontent">
    <div class="controls mb-3">
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

<?php
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/handsontable/handsontable.full.min.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/handsontable-ru-locale.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/settings.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/column-configs.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/column-controls.js");
?>

<script>
    // Функция для преобразования дат в правильный формат
    function formatDateForTable(dateString) {
        if (!dateString) return '';

        if (typeof dateString === 'string' && dateString.match(/^\d{2}\.\d{2}\.\d{4}$/)) {
            return dateString;
        }

        try {
            var date = new Date(dateString);
            if (!isNaN(date.getTime())) {
                var day = String(date.getDate()).padStart(2, '0');
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = date.getFullYear();
                return day + '.' + month + '.' + year;
            }
        } catch (e) {
            console.error('Ошибка форматирования даты:', e);
        }

        return dateString;
    }

    // Создаем JavaScript массив с данными исполнений
    var executionsArray = <?= CUtil::PhpToJSObject($executionsData, false, true) ?>;

    // Примените форматирование к данным перед инициализацией таблицы
    executionsArray.forEach(function(item) {
        ['planned_date', 'created_date', 'completion_date', 'publication_date'].forEach(function(field) {
            if (item[field]) {
                item[field] = formatDateForTable(item[field]);
            }
        });
    });

    // Создаем JavaScript объект с русскими названиями полей
    var fieldLabels = <?= CUtil::PhpToJSObject($fieldLabels, false, true) ?>;

    // Поля, которые можно редактировать
    var editableFields = <?= CUtil::PhpToJSObject($editableFields, false, true) ?>;

    // Глобальные переменные
    window.executionsData = executionsArray;
    window.executionsFieldLabels = fieldLabels;
    window.hotTable = null;
    window.executionsArray = executionsArray; // Для доступа в конфиге

    // Инициализация Handsontable
    document.addEventListener('DOMContentLoaded', function() {

        // Регистрируем русский язык для Handsontable
        Handsontable.languages.registerLanguageDictionary(window.handsontableRuLocale);

        var container = document.getElementById('handsontable-container');

        // template.php - в секции <script>
        console.log('Данные исполнений:', executionsArray);
        console.log('Поля статусов:', executionsArray.map(item => ({id: item.id, status: item.status})));

        if (executionsArray.length > 0) {
            initializeTable();
        } else {
            container.innerHTML = '<div class="alert alert-info">Нет данных об исполнениях для отображения</div>';
        }
    });

    // Функция инициализации таблицы
    function initializeTable() {
        var container = document.getElementById('handsontable-container');

        // Подготавливаем колонки для Handsontable используя общую функцию
        var columns = Object.keys(fieldLabels).map(function(key) {
            return window.columnConfigs.getColumnConfig(key, fieldLabels, editableFields);
        });

        // Создаем Handsontable с настройками из конфига
        var config = Object.assign({}, window.handsontableConfig, {
            data: executionsArray,
            columns: columns
        });

        window.hotTable = new Handsontable(container, config);

        // Инициализируем менеджер колонок после создания таблицы
        window.columnManager = new ColumnManager(window.hotTable, fieldLabels, editableFields);
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
</script>