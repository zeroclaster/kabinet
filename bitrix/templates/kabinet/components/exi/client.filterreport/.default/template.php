<?
use Bitrix\Main\Localization\Loc as Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');

$SEARCH_RESULT = $arResult['SEARCH_RESULT'];
?>

<div class="mt-1 clint-filter-report">
    <!-- Блок "Требует вашего внимания" вынесен перед фильтром -->
    <div class="row mb-2">
        <div class="col-md-12 d-flex  justify-content-start align-items-center">
            <!-- Кнопка переключения фильтра -->
            <button type="button" class="btn btn-outline-secondary btn-sm mr-3" id="filter-toggle-btn">
                <span class="filter-toggle-text">Фильтр</span>
                <span class="filter-toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </button>

            <!-- Блок "Требует вашего внимания" -->
            <div class="alert-status-find">
                <input id="alertfind" name="alert" type="checkbox" value="y" <?if($SEARCH_RESULT['alert']) echo 'checked';else echo '';?>>
                <label for="alertfind" class="alert-no-checked <?if($arResult['count_alert']) echo "alert-checked";?>">
                    Требует вашего внимания:
                    <span class="badge badge-iphone-style"><?=$arResult['count_alert']?></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Форма фильтра -->
    <div id="filter-form-container" style="display: none;">
        <form action="" name="clientfindreportform" enctype="multipart/form-data" method="post">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex no-d-flex">
                        <div class="d-flex align-items-center"><button type="button" class="btn btn-link btn-sm">За период</button></div>
                        <div class="mr-2 d-flex align-items-center">с</div>
                        <div class="mr-2"><input name="fromdate1" id="fromdate1" value="<?=$SEARCH_RESULT['fromdate1']?>" type="text" class="form-control" style="width: 123px;"></div>
                        <div class="mr-2 d-flex align-items-center">по</div>
                        <div><input name="todate1" id="todate1" value="<?=$SEARCH_RESULT['todate1']?>" type="text" class="form-control" style="width: 123px;"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex no-d-flex">
                        <div class="d-flex align-items-center mr-3" style="width: 100px;">Со статусом:</div>
                        <div class="d-flex align-items-center">
                            <select name="statusfind" id="statusfind" class="form-control">
                                <option value=""></option>
                                <?foreach ($runnerManager->getStatusList() as $status_id => $title):?>
                                    <option value="<?=$status_id?>" <?if (is_numeric($SEARCH_RESULT['statusfind']) && $SEARCH_RESULT['statusfind']==$status_id) echo "selected"?>><?=$title?></option>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="d-flex align-items-center" style="width: 104px;">Исполнение #</div>
                        <div class="d-flex align-items-center">
                            <input class="form-control" type="text" name="queue" value="<?=$SEARCH_RESULT['queue']?>" style="width: 121px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-md-center mt-3">
                <div class="col-md-8 text-center">
                    <button type="submit" class="btn btn-primary mr-5">Показать</button>
                    <button id="clearform" type="button" class="btn btn-link btn-sm">Все</button>
                    <input type="hidden" name="clearflag" value="">
                </div>
            </div>
        </form>
    </div>
</div>

<?
$jsParams = [
    'SEARCH_RESULT' => $arResult['SEARCH_RESULT'],
    'COMPONENT_NAME' => $this->GetComponent()->getName() // Добавляем имя компонента для уникальности куки
];
?>
<script>
    client_filter_report.start(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>