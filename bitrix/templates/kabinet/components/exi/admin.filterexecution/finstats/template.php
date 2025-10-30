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

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');

$SEARCH_RESULT = $arResult['SEARCH_RESULT'];

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>

<form action="" name="filterform1" enctype="multipart/form-data" method="post">
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-publicdatefrom">Дата публикации</label>
                </div>
                <div class="col-sm-8">
                    <div class="d-flex">
                        <div>
                            <input value="<?=$SEARCH_RESULT['publicdatefromsearch']?>" name="publicdatefromsearch" id="search-publicdatefrom" class="form-control form-control-sm" type="text" style="width: 123px;">
                        </div>
                        <div class="d-flex align-items-center ml-3 mr-3"> - </div>
                        <div>
                            <input value="<?=$SEARCH_RESULT['publicdatetosearch']?>" name="publicdatetosearch" id="search-publicdateto" class="form-control form-control-sm" type="text" style="width: 123px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-md-8 text-center">
            <button type="submit" class="btn btn-primary mr-5">Показать</button>
            <button type="button" id="clearfilter" class="btn btn-outline-secondary">Сбросить фильтр</button>
        </div>
    </div>
</form>

<?
$jsParams = [
    'SEARCH_RESULT' => $arResult['SEARCH_RESULT']
];
?>
<script>
    filter1.init(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>