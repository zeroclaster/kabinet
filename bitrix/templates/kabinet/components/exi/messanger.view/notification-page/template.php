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
?>
<?/*
шаблон
data-usermessanger="notification"
bitrix/templates/kabinet/assets/js/kabinet/vue-componets/messanger/templates/user.notification.js
нет кнопки отправить, только чтение

*/?>
<div id="messangerblock" class="form-group" data-ckeditor="" data-vuerichtext="" data-usermessanger="notification"></div>

<script type="text/html" id="messangerviewtemolate">
    <section class="section-xs">
        <div class="container-fluid">
            <div class="row row-30">
                <div class="col-md-12">
                    <div class="panel">
                            <messangerperformances :projectID="0" :taskID="0" :targetUserID="datauser.ID" :queue_id="0"/>
                    </div>
                </div>
            </div>
        </div>
    </section>
</script>

<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','userStore');
?>
<script>
    components.messangerUsernotification = {
        selector: "[data-usermessanger='notification']",
        script: [
            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/messanger/templates/user.notification.js',
            './js/kabinet/vue-componets/messanger/messanger.factory.js'
        ],
        styles: './css/messanger.css',
        dependencies:'vuerichtext',
        init:null
    }
    var messangerperformances = null;
    window.addEventListener("components:ready", function(event) {
        const messangerSystem2 = createMessangerSystem();
        messangerperformances = messangerSystem2.component.start(<?=CUtil::PhpToJSObject([
            'VIEW_COUNT' => $arParams['COUNT'],
            'TEMPLATE' => 'messangerTemplate'
        ], false, true)?>);
        messangerSystem2.store().$patch({ datamessage: <?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?> });

        const messangerViewApplication = BX.Vue3.BitrixVue.createApp({
            data() {
                return {
                }
            },
            computed: {
                ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
            },
            components: {
                messangerperformances
            },
            // language=Vue
            template: '#messangerviewtemolate'
        });

        configureVueApp(messangerViewApplication,'#messangerblock');
    });
</script>
