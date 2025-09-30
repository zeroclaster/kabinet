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
<section class="section-xs" style="position: relative">
    <div class="container-fluid form-group form-brief" id="kabinetcontent" data-ckeditor="111" data-select2="erytr" data-formbrief=""></div>
</section>


<script type="text/html" id="kabinet-content">

    <section class="">
        <div class="container-fluid">
            <div class="d-flex no-d-flex justify-content-between">
                <div>
                    <h4 v-if="fields.UF_NAME_ORIGINAL != ''">Проект: {{fields.UF_NAME_ORIGINAL}}</h4>
                    <div class="h1"><i class="fa fa-list" aria-hidden="true"></i> Бриф проекта</div>
                </div>
             </div>
        </div>
    </section>

<div class="panel">
    <div class="panel-body">

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


    <?/*
ФИЛЬТР
*/?>
    <?/*
ФИЛЬТР
*/?>
    <div class="brief-field-filter">
        <div class="d-flex justify-content-center">
            <button class="btn btn-link d-flex align-items-center" type="button" @click="toggleFilterView">
                <template v-if="filterView == 'showRequire'">
                    <span class="mr-2">ПОКАЗАТЬ ВСЕ ПОЛЯ</span>
                    <i class="fa fa-angle-down" aria-hidden="true" style="font-size: 31px;"></i>
                </template>
                <template v-else>
                    <span class="mr-2">ПОКАЗАТЬ ТОЛЬКО ОБЯЗАТЕЛЬНЫЕ ПОЛЯ</span>
                    <i class="fa fa-angle-up" aria-hidden="true" style="font-size: 31px;"></i>
                </template>
            </button>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
        </div>
        <div class="col-sm-6">
            <button  class="btn btn-block btn-primary" type="button" @click="saveentity">Сохранить</button>
        </div>
        <div class="col-sm-3">
            <?if($arParams["ID"]):?>
            <a  class="btn btn-block btn-primary" href="/kabinet/projects/planning/?p=<?=$arParams["ID"]?>">Перейти к проекту</a>
            <?endif;?>
        </div>
    </div>
</form>
    </div>
</div>
</script>
<? (\KContainer::getInstance())->get('orderStore'); ?>

<script>
const projectFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_PROJECT'], false, true)?>;
const infoFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_INFOPROJECT'], false, true)?>;
const detailsFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_DETAILSPROJECT'], false, true)?>;
const targetFormStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_TARGETPROJECT'], false, true)?>;

const projectSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_PROJECT_SETTINGS'], false, true)?>;
const infoSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_INFOPROJECT_SETTINGS'], false, true)?>;
const detailsSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_DETAILSPROJECT_SETTINGS'], false, true)?>;
const targetSettingsStoreData = <?=CUtil::PhpToJSObject($arResult['DATA_TARGETPROJECT_SETTINGS'], false, true)?>;

    components.formbrief = {
        selector: '[data-formbrief]',
        script: [
            './js/kabinet/vue-componets/extension/addnewmethods.js',
            './js/kabinet/vue-componets/richtext.js',
            './js/kabinet/vue-componets/customoption.js',
            './js/kabinet/vue-componets/photoload.js',
            '../../kabinet/components/exi/form.brief/.default/brief_form.js'
        ],
        init:null
    }

const  projectFormStore = BX.Vue3.Pinia.defineStore('projectForm', {
    state: () => ({
        fields:projectFormStoreData,
        projectsettings: projectSettingsStoreData,
    })
});

const  infoFormStore = BX.Vue3.Pinia.defineStore('infodataForm', {
    state: () => ({
        fields2:infoFormStoreData,
        infosettings:infoSettingsStoreData,
    })
});

const  detailsFormStore = BX.Vue3.Pinia.defineStore('detailsForm', {
    state: () => ({
        fields3:detailsFormStoreData,
        detailssettings:detailsSettingsStoreData,
    })
});

const  targerFormStore = BX.Vue3.Pinia.defineStore('targerForm', {
    state: () => ({
        fields4:targetFormStoreData,
        targetsettings:targetSettingsStoreData,
    })
});

        const PHPPARAMS = <?=CUtil::PhpToJSObject(["PROJECT_ID"=>$arParams["ID"],], false, true)?>;
        window.addEventListener("components:ready", function(event) {
            const formApplication = BX.Vue3.BitrixVue.createApp(form_brief);
            configureVueApp(formApplication);
        });
</script>
