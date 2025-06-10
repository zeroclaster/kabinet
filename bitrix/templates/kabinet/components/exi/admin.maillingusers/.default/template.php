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

<div id="kabinetcontent" class="form-group" data-loadtable="" data-maillingusers="">
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
                    <td>
                        {{showFieldEnum(userdata.ID,userdata.UF_EMAIL_NOTIFI)}}
                        <div v-if="userdata.UF_EMAIL_NOTIFI==0">Уведомления на E-mail отключены!</div>
                    </td>

                    <td>
                        <button type="button" class="btn btn-primary" @click="disableEmailSender(userdata.ID)" v-if="userdata.UF_EMAIL_NOTIFI>0">отключить уведомления на Email</button>
                        <div v-if="userdata.UF_EMAIL_NOTIFI==0"></div>
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
        <div class="d-flex align-items-center">Найдено {{total}}, показано {{viewedcount}}</div>
        <div v-if="showloadmore" class="ml-3"><button class="btn btn-primary" type="button" @click="moreload">Показать еще +{{countview}}</button></div>
    </div>

    <div class="alert alert-danger" role="alert" v-if="!telegramusers">
        Нет данных о пользователе!
    </div>

</script>


<script>
    const filtertelegramuserlist = <?=CUtil::PhpToJSObject($arParams["FILTER"], false, true)?>;
    components.contractform = {
        selector: '[data-maillingusers]',
        script: (function() {
            const basePath = './js/kabinet';
            const vueExt = `${basePath}/vue-componets/extension`;
            const taskDef = `../../kabinet/components/exi/admin.maillingusers/.default`;

            return [
                // Vue components
                ...[
                    'addnewmethods.js'
                ].map(file => `${vueExt}/${file}`),

                // Contract Form components
                ...[
                    'maillingusers_list.js'
                ].map(file => `${taskDef}/${file}`)
            ];
        })(),
        init: null
    };

    window.addEventListener("components:ready", function(event) {
        const maillingUsersApplication = BX.Vue3.BitrixVue.createApp({
            ...telegram_users,
            data() {
                return <?=CUtil::PhpToJSObject([
                    "telegramusers" => $arResult['DATA'],
                    "countview" => $arParams["COUNT"],
                    "total" => $arResult["TOTAL"],
                    "showloadmore"=>true
                ], false, true)?>;
            }
        });

        configureVueApp(maillingUsersApplication);
    });
</script>
