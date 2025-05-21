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


<script type="text/html" id="kabinet-content">
    <div class="panel report-list-block">

        <div class="panel-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="col">Плановая дата исполнения</th>
                    <th scope="col">Текст и согласование</th>
                    <th scope="col">
							<div class="d-flex">
									<div class="d-flex align-items-center">Статус, действия, отчет</div>
									<div class="ml-auto">
											<form action="" name="formagreeeverything" method="post">
												<input type="hidden" name="greeeverything" value="y">
												<button type="submit" class="btn btn-primary btn-sm" v-if="isViewSoglasovat()">Согласовать все</button>
											</form>
									</div>
							</div>
					</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(runner,runnerindex) in datarunner">
                    <td style="width: 5%">
                        <div class="text-primary plane-date">
                        <i class="fa fa-calendar" aria-hidden="true"></i> {{runner.UF_PLANNE_DATE_ORIGINAL.FORMAT1}}
                        </div>
                    </td>

                    <td width="30%">
                        <div>#{{runner.UF_EXT_KEY}} исполнение для задачи {{TaskByIdKey[runner.UF_TASK_ID].UF_NAME}}</div>

                        <!-- ссылка -->
                        <mytypeahead :tindex="runnerindex" :catalog="TaskByIdKey[runner.UF_TASK_ID].UF_TARGET_SITE" v-model="runner.UF_LINK"/>

                        <div class="mb-3" v-if="PRODUCT.PHOTO_AVAILABILITY.VALUE_XML_ID != '<?=\Bitrix\Kabinet\task\Taskmanager::PHOTO_NO_NEEDED?>'">
                            <div class="">Фото:</div>
                            <div class=""  style="position: relative;">
                                <div id="previewrunnerfileimages" class="d-flex flex-wrap">
                                    <div class="preview-img-block-2" v-for="photo in showpiclimits(runner.UF_PIC_REVIEW_ORIGINAL,runnerindex)" :style="'background-image:url('+photo.SRC+')'">
                                        <div @click="removeimg(photo.ID,runnerindex)" class="remove-preview-image"><i class="fa fa-times" aria-hidden="true"></i></div>
                                    </div>
                                    <div class="preview-img-block-2" v-if="runner.UF_PIC_REVIEW_ORIGINAL.length==0"><img src="/bitrix/templates/kabinet/assets/images/product.noimage.png" alt="" style="width: 150px;"></div>

                                    <div class="preview-img-block-2 d-flex justify-content-center align-items-center" v-if="runner.UF_PIC_REVIEW_ORIGINAL.length>limitpics && runner.LIMIT==limitpics">
                                        <button class="btn btn-secondary show-all-butt" type="button" @click="showall(runner)">...еще {{runner.UF_PIC_REVIEW_ORIGINAL.length}}</button>
                                    </div>

                                    <sharephoto :tindex="runnerindex" :catalog="TaskByIdKey[runner.UF_TASK_ID].UF_PHOTO_ORIGINAL" v-model="runner.UF_PIC_REVIEW"/>
                                </div>
                            </div>
                        </div>



                        <template v-if="PRODUCT.COORDINATION.VALUE_XML_ID == '<?=\Bitrix\Kabinet\task\Taskmanager::IS_SOGLACOVANIE?>'">
                        <div class="form-group">
                            <div>Текст:</div>
                            <div class="richtext-height-200_">
                                <?/*
                                    параметр autosave="y" включает автосохранение
                                */?>
                                <richtext :tindex="runnerindex" showsavebutton="y"  :original="runner.UF_REVIEW_TEXT_ORIGINAL" v-model="runner.UF_REVIEW_TEXT" :placeholder="'Текст для публикации...'"/>
                            </div>
                        </div>
                        </template>

                        <template v-if="TaskByIdKey[runner.UF_TASK_ID].UF_MANAGER_ID>0">
                            <div class="plug-block-writecomment"  v-if="!hiddenCommentBlock.isShow(runner)" @click="hiddenCommentBlock.mclick(runner)"><i class="fa fa-chevron-down" aria-hidden="true"></i> Написать комментарий...</div>
                            <div v-if="hiddenCommentBlock.isShow(runner)">
                            <messangerperformances :projectID="TaskByIdKey[runner.UF_TASK_ID].UF_PROJECT_ID" :taskID="runner.UF_TASK_ID" :queue_id="runner.ID" :targetUserID="TaskByIdKey[runner.UF_TASK_ID].UF_MANAGER_ID"/>
                            </div>
                        </template>
                        
                    </td>

                    <td style="width: 20%">
                        <div :class="'alert alert-only-text abcs-1'+' '+alertStyle(runner.UF_STATUS)"><i :class="'fa '+runner.UF_STATUS_ORIGINAL.ICON" aria-hidden="true"></i> {{runner.UF_STATUS_ORIGINAL.TITLE}} с {{runner.UF_CREATE_DATE_ORIGINAL.FORMAT1}}</div>

                        <changestatus :catalog="runner.STATUSLIST" :tindex="runnerindex" v-model="runner.UF_STATUS"/>
                        <commentWrite :tindex="runnerindex" ref="modaleCommnetWrite" v-model="runner.UF_COMMENT"/>


                        <div class="mt-4 p-3 report-link-block" v-if="isShowReportLink(runnerindex)">
                            <div class="blk-title">Отчет:</div>
                            <div v-if="runner.UF_REPORT_LINK_ORIGINAL">Ссылка: <a :href="runner.UF_REPORT_LINK_ORIGINAL" target="_blank" rel="nofollow">Открыть</a></div>
                            <div v-if="runner.UF_REPORT_SCREEN_ORIGINAL">Скриншот: <a :href="runner.UF_REPORT_SCREEN_ORIGINAL" target="_blank" rel="nofollow">Смотреть</a></div>
                            <div v-if="runner.UF_REPORT_FILE_ORIGINAL">Файл: <a :href="runner.UF_REPORT_FILE_ORIGINAL" target="_blank" rel="nofollow">Скачать</a></div>
                            <div v-if="runner.UF_REPORT_TEXT_ORIGINAL">{{runner.UF_REPORT_TEXT_ORIGINAL}}</div>
                        </div>
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

    <shownote />
</script>

<? (\KContainer::getInstance())->get('orderStore','briefStore','taskStore'); ?>
<script>
    const  runnerlistStore = BX.Vue3.Pinia.defineStore('runnerlist', {
        state: () => ({datarunner:<?=CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>}),
    });

    var shownote = null;
    components.messangerUser = {
        selector: "[data-usermessanger='report']",
        script: [
            './js/kabinet/vue-componets/datepicker.js',
            './js/kabinet/vue-componets/sharephoto.js',
            '../../kabinet/components/exi/profile.user/.default/user.data.php',
            '../../kabinet/components/exi/reports.list/.default/reports.list.js',

            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/messanger/templates/user.report.js',
            './js/kabinet/vue-componets/messanger/messanger.factory.js',
        ],
        styles: './css/messanger.css',
        dependencies:'vuerichtext',
        init:null
    }

    components.userreports = {
        selector: '[data-userreports]',
        script: [
            '../../kabinet/assets/js/kabinet/vue-componets/show.note.js',
            '../../kabinet/components/exi/task.list/.default/data_helper.js',
            '../../kabinet/components/exi/reports.list/.default/js/hiddenCommentBlock.js'
        ],
        init:null
    }

        window.addEventListener("components:ready", function(event) {

            const messangerSystem2 = createMessangerSystem();
            messangerperformances = messangerSystem2.component.start(<?=CUtil::PhpToJSObject([
                'VIEW_COUNT' => $arParams['MESSAGE_COUNT'],
                'TEMPLATE' => 'messangerTemplate'
            ], false, true)?>);
            messangerSystem2.store().$patch({ datamessage: <?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?> });

        shownote = showmessage_vuecomponent.start(<?=CUtil::PhpToJSObject(["note" => $arResult['note'],], false, true)?>);

        reports_list.start(<?=CUtil::PhpToJSObject([
            "TASK_ID"=>$arParams['TASK_ID'],
            "FILTER"=>$arParams["FILTER"],
            "viewcount"=>$arParams["COUNT"],
            "total"=>$arResult["TOTAL"],
            "statuslistdata" => $runnerManager->getStatusList()
        ], false, true)?>,'<?= $this->getComponent()->getSignedParameters() ?>');
    });
</script>


