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

<div id="messangerblock" class="form-group" data-ckeditor="" data-vuerichtext="" data-usermessanger="project"></div>

<script type="text/html" id="messangerviewtemolate">
    <section class="section-xs">
        <div class="container-fluid">
            <div class="row row-30">
                <div class="col-md-12">

                    <h2>Уведомления</h2>

                    <div class="panel">
                            <messangerperformances :projectID="<?=$arParams["FILTER"]['UF_PROJECT_ID']?>" :taskID="0" :targetUserID="datauser.ID" :queue_id="0"/>
                    </div>
                </div>
            </div>
        </div>
    </section>
</script>


<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','userStore');
Asset::getInstance()->addJs($templateFolder."/messanger.view.js");
?>

<script>
    components.messangerUserProject = {
        selector: "[data-usermessanger='project']",
        script: [
            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/messanger/templates/user.project.js',
            './js/kabinet/vue-componets/messanger/messanger2.js',
        ],
        styles: './css/messanger.css',
        dependencies:'vuerichtext',
        init:null
    }

    const messageStore = BX.Vue3.Pinia.defineStore('messagelist', {
        state: () => ({datamessage:<?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?>}),
    });

    window.addEventListener("components:ready", function(event) {

        var m = <?=CUtil::PhpToJSObject(['VIEW_COUNT' => $arParams['COUNT']], false, true)?>;
        m.TEMPLATE = messangerTemplate;
        m.messageStoreInst = function(){
            return function () {
                return messageStore();
            }
        };
        m.messageStore = messageStore;

        let messanger_vuecomponent2_2 = { ...messanger_vuecomponent2 }
        messangerperformances = messanger_vuecomponent2_2.start(m);

        messanger_view.start(<?=CUtil::PhpToJSObject([], false, true)?>);
    });
</script>
