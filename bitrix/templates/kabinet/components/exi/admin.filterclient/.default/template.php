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
?>

<form action="" name="filterform1" enctype="multipart/form-data" method="post">
    <div class="row justify-content-md-center">
        <div class="col-md-4">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="search-client">Клиент</label>
                </div>
                <div class="col-sm-9">
                    <input id="clientidsearch" name="clientidsearch" type="hidden">
                    <input value="<?=$arResult['SEARCH_RESULT']['clienttextsearch']?>" name="clienttextsearch" id="search-client" class="form-control" type="text" placeholder="начните вводить или выберите из списка" data-typehead=''>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="search-project">Проект</label>
                </div>
                <div class="col-sm-9">
                    <input id="projectidsearch" name="projectidsearch" type="hidden">
                    <input value="<?=$arResult['SEARCH_RESULT']['projecttextsearch']?>" name="projecttextsearch" id="search-project" class="form-control" type="text" placeholder="начните вводить или выберите из списка" data-typehead='[]'>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-md-center">
        <div class="col-md-4">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="search-task">Задачи</label>
                </div>
                <div class="col-sm-9">
                    <input id="taskidsearch" name="taskidsearch" type="hidden">
                    <input value="<?=$arResult['SEARCH_RESULT']['tasktextsearch']?>" name="tasktextsearch" id="search-task" class="form-control" type="text" placeholder="начните вводить или выберите из списка" data-typehead='[]'>
                </div>
            </div>
        </div>
        <div class="col-md-4">

        </div>
    </div>
    <div class="row justify-content-md-center">
        <div class="col-md-8 text-center">
            <button type="submit" class="btn btn-primary mr-5"><i class="fa fa-filter" aria-hidden="true"></i> Показать</button> Показать: <a href="#" id="clearfilter">Все</a>
        </div>
    </div>
</form>


<?
$jsParams = [
        'SEARCH_RESULT' => $arResult['SEARCH_RESULT']
];
?>
<script>
    // installe
    filter1.init(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>