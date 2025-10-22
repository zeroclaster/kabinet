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

$runnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');
?>

<div id="kabinetcontent" data-typehead="" data-vuetypeahead="" data-datetimepicker="" data-loadtable="" data-modalload="" data-ckeditor="" data-vuerichtext="" data-usermessanger="report" data-userreports=""></div>

<script type="text/html" id="sharephoto-template">
    <div class="preview-img-block-2 addbutton2 d-flex justify-content-center align-items-center" @click="showmodale" v-if="isEdit()">
        <span class="add-images-marker-2">+</span>
    </div>
    <!-- Modal -->
    <div class="modal fade" :id="ModalID" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel">Галерея</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="">Выбрать фото из задачи:</div>
                        <div class="gallery-modal-1"  style="position: relative;">
                            <div id="previewrunnerfileimages" class="d-flex flex-wrap">
                                <div class="preview-img-block-2 back-img-share" v-for="photo in catalog" @click="selphoto(photo.ID)" :style="'background-image: url('+photo.SRC+');'">

                                    <div class="selected-image" v-if="isSelectedPhohto(photo.ID)"><i class="fa fa-check" aria-hidden="true"></i></div>
                                </div>
                                <div class="preview-img-block-2" v-if="catalog.length==0"><img src="/bitrix/templates/kabinet/assets/images/product.noimage.png" alt="" style="width: 150px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" v-if="selectedPhohto.length==0">
                        <div class="custom-loadphoto">
                            <button class="btn btn-primary" type="button"><i class="fa fa-cloud-download" aria-hidden="true"></i> Или загрузить с устройства</button>
                            <input type="file" @change="onChangeFile" name="file"  multiple/>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="addphoto" :disabled="notload">Добавить</button>
                    <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

</script>

<?
//include_once(__DIR__.'/mobile.inc.php');


if (isMobileDevice())
include_once(__DIR__.'/mobile.inc.php');
else
include_once(__DIR__.'/desctop.inc.php');

?>
<? (\KContainer::getInstance())->get('orderStore','briefStore','taskStore'); ?>
<script>
    const  runnerlistStore = BX.Vue3.Pinia.defineStore('runnerlist', {
        state: () => ({datarunner:<?=CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>}),
    });

    var messangerperformances = {}
    var shownote = {};

    components.userreports = {
        selector: '[data-userreports]',
        script: [
            './js/kabinet/vue-componets/datepicker.js',
            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/sharephoto.js',
            '../../kabinet/assets/js/kabinet/vue-componets/show.note.js',
            '../../kabinet/components/exi/task.list/.default/data_helper.js',
            '../../kabinet/components/exi/reports.list/.default/js/hiddenCommentBlock.js',
            '../../kabinet/components/exi/reports.list/.default/js/commentwrite.js',
            '../../kabinet/components/exi/reports.list/.default/js/changestatus.js',
            '../../kabinet/components/exi/reports.list/.default/reports.list.js',
        ],
        init:null
    }

    components.messangerUser = {
        selector: "[data-usermessanger='report']",
        script: [
            '../../kabinet/components/exi/profile.user/.default/user.data.php',
            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/messanger/templates/user.report.js',
            './js/kabinet/vue-componets/messanger/messanger.factory.js',
        ],
        styles: './css/messanger.css',
        dependencies:'vuerichtext',
        init:null
    }


        const PHPPARAMS = <?=CUtil::PhpToJSObject([
            "TASK_ID"=>$arParams['TASK_ID'],
            "FILTER"=>$arParams["FILTER"],
            "viewcount"=>$arParams["COUNT"],
            "total"=>$arResult["TOTAL"],
            "statuslistdata" => $runnerManager->getStatusList()
        ], false, true)?>;

        var filterclientlist = PHPPARAMS.FILTER;

        const signedParameters = '<?= $this->getComponent()->getSignedParameters() ?>';

        var messageStore = null;
        var messageStoreInstance = null;

        window.addEventListener("components:ready", function(event) {

            const messangerSystem2 = createMessangerSystem();
            messageStoreInstance = messangerSystem2.store();
            reportsListApplicationConfig.components.messangerperformances = messangerSystem2.component.start(<?=CUtil::PhpToJSObject([
                'VIEW_COUNT' => $arParams['MESSAGE_COUNT'],
                'TEMPLATE' => 'messangerTemplate'
            ], false, true)?>);
            messageStoreInstance.$patch({ datamessage: <?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?> });

            // Дополнительно сохраняем в глобальной переменной для обратной совместимости
            window.messageStoreInstance = messageStoreInstance;

            messageStore = messangerSystem2.store();

                reportsListApplicationConfig.components.shownote = showmessage_vuecomponent.start(<?=CUtil::PhpToJSObject(["note" => $arResult['note'],], false, true)?>);

            /*
            Object.assign(reportsListApplicationConfig.components, {
                sizeBuy,
                OrderBuy
            });

             */

            const reportsListApplication = BX.Vue3.BitrixVue.createApp(reportsListApplicationConfig);
            configureVueApp(reportsListApplication);
    });
</script>


