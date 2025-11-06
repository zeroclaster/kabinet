<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

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
//\Dbg::var_dump($arResult);

$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$usertype = \CUserOptions::GetOption('kabinet','usertype',false,$user->get('ID'));

?>
<div class="panel-body" id="kabinetcontent" data-contractform="" style="padding-top: 0px;"></div>

<script type="text/html" id="kabinet-content">

<div class="panel">
    <div class="panel-body">
<form action="" method="post" enctype="multipart/form-data">
    <? foreach ($arParams['GROUPS'] as $key => $GROUP_TITLE):?>
        <div class="form-group">
            <div class="text-center h3 mb-3 mt-0"><?=$GROUP_TITLE?></div>
        </div>

        <?if($key==0):?>
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="contract_type">Договор заключается от имени:</label>
                </div>
                <div class="col-sm-6">
                    <select class="form-control" id="contract_type" v-model="contracttype.value">
                        <option value="1" selected>Физического лица</option>
                        <option value="2">Индивидуального предпринимателя</option>
                        <option value="3">Директора организации</option>
                        <option value="4">Генерального директора организации</option>
                    </select>
                </div>
                <div class="col-sm-3 form-help-message"></div>
            </div>
        <?endif;?>
        <? foreach ($arParams['GROUP'.$key] as $fieldParams):?>

            <div class="row form-group" v-if="isShowfield(<?=CUtil::PhpToJSObject($fieldParams['TYPE_VIEW'], false, true)?>)">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="<?=$component->makeId($fieldParams)?>"><?=$fieldParams['FIELD_TITLE_VIEW']?></label>
                </div>
                <div class="col-sm-6"><?=$fieldParams['PUBLIC_EDIT']?></div>

                <?if($fieldParams['FIELD_NAME'] != 'UF_ACTS'):?>
                <div class="col-sm-3 form-help-message"><?=$fieldParams["HELP_MESSAGE"]?></div>
                <?else:?>
                    <div class="col-sm-3 form-help-message" v-if="contracttype.value==1">Укажите паспортные данные</div>
                    <div class="col-sm-3 form-help-message" v-if="contracttype.value==2">Листа записи в ЕГРИП. Или “Свидетельства о государственной регистрации”, если ИП зарегистрирован до 2017 года.</div>
                    <div class="col-sm-3 form-help-message" v-if="contracttype.value==3">Устава или № Доверенности.</div>
                    <div class="col-sm-3 form-help-message" v-if="contracttype.value==4">Устава или № Доверенности.</div>
                <?endif;?>
            </div>

        <?endforeach;?>
    <?endforeach;?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
        </div>
        <div class="col-sm-6">
            <button  class="btn btn-block btn-primary" type="button" @click="saveentity">Сохранить</button>
        </div>
    </div>
</form>
    </div>
</div>

</script>


<script>
    components.contractform = {
        selector: '[data-contractform]',
        script: (function() {
            const basePath = './js/kabinet';
            const vueExt = `${basePath}/vue-componets/extension`;
            const taskDef = `../../kabinet/components/exi/form.contract/.default`;

            return [
                // Vue components
                ...[
                    'addnewmethods.js'
                ].map(file => `${vueExt}/${file}`),

                // Contract Form components
                ...[
                    'contract_form.js'
                ].map(file => `${taskDef}/${file}`)
            ];
        })(),
        init: null
    };

    const  AgreementFormStore = BX.Vue3.Pinia.defineStore('agreementForm', {
        state: () => ({
            fields:<?=CUtil::PhpToJSObject($arResult['DATA'], false, true)?>,
            contractsettings: <?=CUtil::PhpToJSObject($arResult['DATA_CONTRACT_SETTINGS'], false, true)?>,
            fields2:<?=CUtil::PhpToJSObject($arResult['DATA2'], false, true)?>,
            banksettings: <?=CUtil::PhpToJSObject($arResult['DATA_BANK_SETTINGS'], false, true)?>,
            contracttype:{value:<?=CUtil::PhpToJSObject($usertype, false, true)?>},
        })
    });

        window.addEventListener("components:ready", function(event) {
            const formApplication = BX.Vue3.BitrixVue.createApp(form_contract);
            configureVueApp(formApplication);
        });
</script>

	