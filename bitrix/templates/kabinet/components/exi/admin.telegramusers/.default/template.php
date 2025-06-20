<?
use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

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


//print_r($arResult);
?>

<div id="kabinetcontent" class="form-group" data-loadtable="" data-telegramusers="">
</div>

<script type="text/html" id="kabinet-content">
    <div class="panel telegram-users-list-block" v-if="telegramusers">
        <div class="panel-body">

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>ФИО</th>
                    <th>E-mail</th>
                    <th>Уведомления</th>
                    <th>Действие</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="userdata in telegramusers">
                    <td>{{userdata.ID}}</td>
                    <td>{{userdata.FIO}}</td>
                    <td>{{userdata.EMAIL}}</td>
                    <td v-if="userdata.UF_TELEGRAM_ID>0">
                        <input type="checkbox" v-model="userdata.UF_TELEGRAM_NOTFI" @change="saveData(userdata.ID)">
                    </td>
                    <td v-if="userdata.UF_TELEGRAM_ID==0">

                    </td>
                    <td>
                        <button type="button" class="btn btn-primary" @click="disableTELEGRAM(userdata.ID)" v-if="userdata.UF_TELEGRAM_ID>0">отключить TELEGRAM</button>
                        <button type="button" class="btn btn-primary" @click="connectTELEGRAM(userdata.ID)" v-if="userdata.UF_TELEGRAM_ID==0">подключить TELEGRAM</button>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>
    <div class="text-right mt-1">
        показать по: <input name="viewcount" type="text" v-model="countview" style="width: 35px;">
    </div>
    <div class="d-flex justify-content-center">
        <div class="d-flex align-items-center">Найдено {{total}}, показано {{telegramusers?.length || 0}}</div>
        <div v-if="showloadmore" class="ml-3"><button class="btn btn-primary" type="button" @click="moreload">Показать еще +{{countview}}</button></div>
    </div>

    <div class="alert alert-danger" role="alert" v-if="!telegramusers">
        Нет данных о пользователе!
    </div>

</script>


<script>
    const filtertelegramuserlist = <?=CUtil::PhpToJSObject($arParams["FILTER"], false, true)?>;
    components.contractform = {
        selector: '[data-telegramusers]',
        script: (function() {
            const basePath = './js/kabinet';
            const vueExt = `${basePath}/vue-componets/extension`;
            const taskDef = `../../kabinet/components/exi/admin.telegramusers/.default`;

            return [
                // Vue components
                ...[
                    'addnewmethods.js'
                ].map(file => `${vueExt}/${file}`),

                // Contract Form components
                ...[
                    'telegramusers_list.js'
                ].map(file => `${taskDef}/${file}`)
            ];
        })(),
        init: null
    };

    window.addEventListener("components:ready", function(event) {
        const telegramUsersApplication = BX.Vue3.BitrixVue.createApp({
            ...telegram_users,
            data() {
                return <?=CUtil::PhpToJSObject([
                    "telegramusers" => $arResult['DATA'],
                    "countview" => $arParams["COUNT"],
                    "total" => $arResult["TOTAL"]
                ], false, true)?>;
            }
        });

        configureVueApp(telegramUsersApplication);
    });
</script>
