<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

?>
<script type="text/html" id="kabinet-content">

    <section class="">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div>
                    <h4 v-if="fields.UF_NAME_ORIGINAL != ''">Проект: {{fields.UF_NAME_ORIGINAL}}</h4>
                    <div class="h1"><i class="fa fa-list" aria-hidden="true"></i> Бриф проекта</div>
                </div>
             </div>
        </div>
    </section>

<div class="panel">
    <div class="panel-header">
        <h4 class="panel-title"></h4>
    </div>
    <div class="panel-body">
	
	<?/*
	ФИЛЬТР
	*/?>
	<div class="brief-field-filter">
		<div class="d-flex justify-content-center">
		<div class="mr-3 d-flex align-items-center">Показать:</div>
		<div class="mr-3"><button :class="'btn btn-link' + (filterView == 'showRequire'? ' disabled':'')" @click="showRequire" type="button">обязательные поля <?/*({{getRequireFields(fields.UF_ORDER_ID).length}} заполнить)*/?></button></div>
		<div><button :class="'btn btn-link' + (filterView == 'showAll'? ' disabled':'')" type="button" @click="showAll">все поля</button></div>
		</div>
	</div>
	
	
<form action="" method="post" enctype="multipart/form-data">
    <? foreach ($arParams['GROUPS'] as $key => $GROUP_TITLE):?>
        <div class="form-group">
            <?
            $groupFields = [];
            foreach ($arParams['GROUP'.$key] as $fieldParams) $groupFields[] = $fieldParams['FIELD_NAME'];
            ?>
            <div :class="'text-center h3 mb-3 mt-5 ' + isViewGroupTitle(<?=CUtil::PhpToJSObject($groupFields, false, true)?>)"><?=$GROUP_TITLE?></div>
        </div>
        <? foreach ($arParams['GROUP'.$key] as $fieldParams):?>

            <div :class="'row form-group '+isRequire('<?=$fieldParams['FIELD_NAME']?>')+isView('<?=$fieldParams['FIELD_NAME']?>')" v-if="isShowfield(<?=CUtil::PhpToJSObject($fieldParams['TYPE_VIEW'], false, true)?>)">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="<?=$component->makeId($fieldParams)?>"><?=$fieldParams['FIELD_TITLE_VIEW']?></label>
                </div>
                <div class="col-sm-6"><?=$fieldParams['PUBLIC_EDIT']?></div>
                <div class="col-sm-3 form-help-message"><?=$fieldParams["HELP_MESSAGE"]?></div>
            </div>

        <?endforeach;?>
    <?endforeach;?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
        </div>
        <div class="col-sm-6">
            <button  class="btn btn-block btn-primary" type="button" @click="saveentity">Сохранить</button>
        </div>
        <div class="col-sm-3">
            <a  class="btn btn-block btn-primary" href="/kabinet/projects/planning/?p=<?=$arParams["ID"]?>">Перейти к проекту</a>
        </div>
    </div>
</form>
    </div>
</div>
</script>
<?
(\KContainer::getInstance())->get('orderStore');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/addnewmethods.js");
?>
<?ob_start();?>
<script>
const projectFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_PROJECT'], false, true)?>;
const infoFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_INFOPROJECT'], false, true)?>;
const detailsFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_DETAILSPROJECT'], false, true)?>;
const targetFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_TARGETPROJECT'], false, true)?>;

const projectSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_PROJECT_SETTINGS'], false, true)?>;
const infoSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_INFOPROJECT_SETTINGS'], false, true)?>;
const detailsSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_DETAILSPROJECT_SETTINGS'], false, true)?>;
const targetSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_TARGETPROJECT_SETTINGS'], false, true)?>;
</script>
<script type="text/javascript" src="/bitrix/templates/kabinet/assets/js/kabinet/vue-componets/richtext.js"></script>
<script type="text/javascript" src="/bitrix/templates/kabinet/assets/js/kabinet/vue-componets/customoption.js"></script>
<script type="text/javascript" src="/bitrix/templates/kabinet/assets/js/kabinet/vue-componets/photoload.js"></script>
<script type="text/javascript" src="<?=$templateFolder?>/brief_form.js"></script>

<script>
        window.addEventListener("components:ready", function(event) {
            form_brief.start(<?=CUtil::PhpToJSObject([
                "PROJECT_ID"=>$arParams["ID"],
            ], false, true)?>);
        });
</script>
<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>		