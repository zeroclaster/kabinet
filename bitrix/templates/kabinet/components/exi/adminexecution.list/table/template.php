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
// Подключаем обработчик фильтров
include __DIR__ . '/filter-processor.php';

// Подготавливаем данные для JavaScript
$executionsData = prepareExecutionsData($arResult, $arParams, $runnerManager);

// Получаем информацию о примененных фильтрах
$appliedFilters = processAppliedFilters($arParams, $arResult, $runnerManager);

Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/assets/js/handsontable/handsontable.full.min.css");
?>
<div id="kabinetcontent" class="workarea">
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
                        <?php if ($key !== 'id'): ?>
                            <div class="form-check">
                                <input class="form-check-input column-toggle" type="checkbox"
                                       id="column-<?= $key ?>" data-column="<?= $key ?>" checked>
                                <label class="form-check-label" for="column-<?= $key ?>">
                                    <?= $label ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="column-menu-footer">
                    <button id="selectAllColumns" class="btn btn-sm btn-outline-secondary">Выбрать все</button>
                    <button id="deselectAllColumns" class="btn btn-sm btn-outline-secondary">Снять все</button>
                    <button id="resetColumnSettings" class="btn btn-sm btn-outline-warning">Сбросить настройки</button>
                </div>
            </div>
        </div>

        <!-- Блок с информацией о фильтрах -->
        <div class="filters-info">
            <?php if (!empty($appliedFilters)): ?>
                <div class="filters-line">
                    <?php foreach ($appliedFilters as $filter): ?>
                        <span class="filter-badge">
                            <span class="filter-badge-label"><?= htmlspecialchars($filter['label']) ?>:</span>
                            <span class="filter-badge-value"><?= htmlspecialchars($filter['value']) ?></span>
                        </span>
                    <?php endforeach; ?>
                    <span class="total-badge">
                        Всего: <?= $arResult['TOTAL'] ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="filters-line">
                    <span class="no-filters-badge">Фильтры не применены</span>
                    <span class="total-badge">
                        Всего: <?= $arResult['TOTAL'] ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="filters-line go-back-page" style="width: 50px;">
                <a href="/kabinet/admin/performances/"><i class="fa fa-reply" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
    <div id="handsontable-container"></div>
</div>

<?php
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/handsontable/handsontable.full.min.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/handsontable-ru-locale.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/adminexecution.list/table/table-save-manager.js");
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

    // Функция для расчета высоты таблицы
    function calculateTableHeight() {
        const container = document.getElementById('kabinetcontent');
        const controls = document.querySelector('.controls');
        const headerHeight = 60;
        const margins = 40;

        const windowHeight = window.innerHeight;
        const controlsHeight = controls ? controls.offsetHeight : 0;

        return windowHeight - controlsHeight - headerHeight - margins;
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
    window.executionsArray = executionsArray;

    // Инициализация Handsontable
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('handsontable-no-scroll');
        Handsontable.languages.registerLanguageDictionary(window.handsontableRuLocale);

        var container = document.getElementById('handsontable-container');

        if (executionsArray.length > 0) {
            initializeTable();
        } else {
            container.innerHTML = '<div class="alert alert-info">Нет данных об исполнениях для отображения</div>';
        }
    });

    // Обработка изменения размера окна
    window.addEventListener('resize', function() {
        if (window.hotTable) {
            const newHeight = calculateTableHeight();
            window.hotTable.updateSettings({
                height: newHeight
            });
        }
    });

    // Функция инициализации таблицы
    function initializeTable() {
        var container = document.getElementById('handsontable-container');

        // Подготавливаем колонки для Handsontable, исключая поле id
        var columns = Object.keys(fieldLabels)
            .filter(function(key) {
                return key !== 'id';
            })
            .map(function(key) {
                return window.columnConfigs.getColumnConfig(key, fieldLabels, editableFields);
            });

        const tableHeight = calculateTableHeight();

        var config = Object.assign({}, window.handsontableConfig, {
            data: executionsArray,
            columns: columns,
            height: tableHeight
        });

        window.hotTable = new Handsontable(container, config);
        window.columnManager = new ColumnManager(window.hotTable, fieldLabels, editableFields);
        window.tableSaveManager = new TableSaveManager(window.hotTable, executionsArray, editableFields);
    }

    // Восстанавливаем скролл при размонтировании
    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('handsontable-no-scroll');
    });
</script>