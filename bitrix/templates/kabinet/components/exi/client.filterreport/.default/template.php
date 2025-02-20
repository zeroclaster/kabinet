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
<form action="" name="clientfindreportform" enctype="multipart/form-data" method="post">
<div class="row">

    <div class="col-md-4">
        <div class="d-flex">
            <div class="d-flex align-items-center"><button type="button" class="btn btn-link btn-sm">За период</button></div>

            <div class="mr-2 d-flex align-items-center">с</div>
            <div class="mr-2"><input name="fromdate1" id="fromdate1" value="<?=$SEARCH_RESULT['fromdate1']?>" type="text" class="form-control" style="width: 123px;"></div>
            <div class="mr-2 d-flex align-items-center">по</div>
            <div><input name="todate1" id="todate1" value="<?=$SEARCH_RESULT['todate1']?>" type="text" class="form-control" style="width: 123px;"></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="d-flex">
            <div class="d-flex align-items-center mr-3" style="width: 100px;">Со статусом:</div>
            <div class="d-flex align-items-center">
                <select name="statusfind" id="statusfind" class="form-control">
                    <option value=""></option>
                    <?foreach ($runnerManager->getStatusList() as $status_id => $title):?>
                        <option value="<?=$status_id?>" <?if ($SEARCH_RESULT['statusfind'] && $SEARCH_RESULT['statusfind']==$status_id) echo "selected"?>><?=$title?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-2 align-self-center alert-status-find">
		<input id="alertfind" name="alert" name="alertfind" type="checkbox" value="y" <?if($SEARCH_RESULT['alert']) echo 'checked';?>>
        <label for="alertfind" class="<?if($arResult['count_alert']) echo "alert-checked";?>">Требует вашего внимания:  <span class="badge badge-iphone-style"><?=$arResult['count_alert']?></span></label>
    </div>
    <div class="col-md-3">
        <div class="d-flex">
            <div class="d-flex align-items-center mr-3" style="width: 116px;">ID исполнения:</div>
            <div class="d-flex align-items-center">
                <input class="form-control" type="text" name="queue" value="<?=$SEARCH_RESULT['queue']?>" style="width: 76px;">
            </div>
        </div>
    </div>
</div>

    <div class="row justify-content-md-center">
        <div class="col-md-8 text-center">
            <button type="submit" class="btn btn-primary mr-5">Показать</button> <button id="clearform" type="button" class="btn btn-link btn-sm">Все</button>
        </div>
    </div>
</form>
</div>
<?
$jsParams = [
    'SEARCH_RESULT' => $arResult['SEARCH_RESULT']
];
?>
<script>
    // installe
    client_filter_report.start(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>