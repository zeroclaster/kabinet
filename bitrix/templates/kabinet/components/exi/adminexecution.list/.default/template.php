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
$messanger = $sL->get('Kabinet.Messanger');

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>


<div id="kabinetcontent" data-datetimepicker="" data-loadtable="" data-modalload="" data-ckeditor="" data-vuerichtext="" data-adminmessanger=""></div>

<script type="text/html" id="sharephoto-template">
    <div class="preview-img-block-2 addbutton2 d-flex justify-content-center align-items-center" @click="showmodale">
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
                            <button class="btn btn-primary" type="button">Или загрузить с устройства</button>
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
    <div class="panel admin-execution-list">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            </div>
        </div>

        <div class="panel-body">
<table class="table">
    <thead>
    <tr>
        <th scope="col">Планирование</th>
        <th scope="col">Текст и поля</th>
        <th scope="col">Статус и действия</th>
        <th scope="col">Размещение и отчет</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(runner,runnerindex) in datarunner">
        {{(UF_AUTHOR_ID=datatask[runner.UF_TASK_ID].UF_AUTHOR_ID,null)}}
        {{(UF_PROJECT_ID=datatask[runner.UF_TASK_ID].UF_PROJECT_ID,null)}}
        <td style="width: 20%">
            <div class="mb-3 form-group datepicker-input">
                <div><label class="" for="planedate-execution">Плановая дата публикации</label></div>
                <div class="d-flex">
                    <mydatepicker :tindex="runnerindex" :original="runner.UF_PLANNE_DATE_ORIGINAL.FORMAT1" :mindd="runner.UF_PLANNE_DATE_ORIGINAL.MINDATE" v-model="runner.UF_PLANNE_DATE"/>
                </div>
            </div>

            <div>
                <div class="mb-3">
                    <div>Клиент:</div>
                    <div class="text-primary">{{dataclient[UF_AUTHOR_ID].PRINT_NAME}} (#{{dataclient[UF_AUTHOR_ID].ID}})</div>
                    <div><a href="mailto:{{dataclient[UF_AUTHOR_ID].EMAIL}}"></a></div>
                </div>
                <div class="mb-3">
                    <div>Проект:</div>
                    <div class="text-primary">{{dataproject[UF_PROJECT_ID].UF_NAME}}</div>
                </div>
                <div class="mb-3">
                    <div>Задача:</div>
                    <div class="text-primary">{{datatask[runner.UF_TASK_ID].UF_NAME}}</div>
                    <div style="font-size: 11px;">
                        <div class="info-blk">Дата создания: <span>{{datatask[runner.UF_TASK_ID].UF_PUBLISH_DATE_ORIGINAL.FORMAT1}}</span></div>
                        <div class="info-blk">Дата завершения: <span>{{runner.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</span></div>
                        <div class="info-blk">Согласование: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_COORDINATION')}}</span></div>
                        <div class="info-blk">Отчетность: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_REPORTING')}}</span></div>
                        <div class="info-blk">Тип процесса: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_CYCLICALITY')}}</span></div>
                    </div>
                </div>
            </div>

        </td>

        <td width="40%">
            <div>Исполнение #{{runner.ID}}<div class="alert alert-danger" role="alert" v-if="runner.UF_HITCH == 1">Просроченная задача</div></div>
            <mytypeahead :tindex="runnerindex" :catalog="datatask[runner.UF_TASK_ID].UF_TARGET_SITE" v-model="runner.UF_LINK"/>

            <div class="mb-3" _v-if="runner.UF_ELEMENT_TYPE == 'multiple'">
                Количество запланированных исполнений: {{runner.UF_NUMBER_STARTS}}
            </div>
            
            <div class="mb-3" v-if="datatask[runner.UF_TASK_ID].UF_JUSTFIELD">
                <div class="">{{dataorder[UF_AUTHOR_ID][dataproject[UF_PROJECT_ID].UF_ORDER_ID][datatask[runner.UF_TASK_ID].UF_PRODUKT_ID].JUST_FILED.VALUE}}:</div>
                <input class="form-control" type="text" :value="datatask[runner.UF_TASK_ID].UF_JUSTFIELD">
            </div>

            <div class="mb-3"  v-if="dataorder[UF_AUTHOR_ID][dataproject[UF_PROJECT_ID].UF_ORDER_ID][datatask[runner.UF_TASK_ID].UF_PRODUKT_ID].PHOTO_AVAILABILITY.VALUE_XML_ID != '<?=\Bitrix\Kabinet\task\Taskmanager::PHOTO_NO_NEEDED?>'">
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

                        <sharephoto :tindex="runnerindex" :catalog="datatask[runner.UF_TASK_ID].UF_PHOTO_ORIGINAL" v-model="runner.UF_PIC_REVIEW"/>
                    </div>
                </div>
            </div>

			<!--
            <div class="mb-4 rejected-block" v-if="runner.UF_COMMENT != ''">
                <div class="alert alert-danger" role="alert">Отклонено</div>
                <div class="p-3">{{runner.UF_COMMENT}}</div>
            </div>
			-->

            <template v-if="dataorder[UF_AUTHOR_ID][dataproject[UF_PROJECT_ID].UF_ORDER_ID][datatask[runner.UF_TASK_ID].UF_PRODUKT_ID].VIEW_UF_REVIEW_TEXT.VALUE_XML_ID == '529f3954e3cce751af50dbf5a8f84712'">
            <div>Текст отзыва</div>
            <div class="richtext-height-200_">
                <?/*
                  параметр autosave="y" включает автосохранение
                */?>
                <richtext :tindex="runnerindex" showsavebutton="y"  :original="runner.UF_REVIEW_TEXT_ORIGINAL" v-model="runner.UF_REVIEW_TEXT"/>
            </div>
            </template>

            <messangerperformances :projectID="UF_PROJECT_ID" :taskID="runner.UF_TASK_ID" :targetUserID="UF_AUTHOR_ID" :queue_id="runner.ID"/>

        </td>

        <?/*
        	С т а т у с    и    д е й с т в и я
        */?>
        <td>
                <div :class="'alert '+alertStyle(runner.UF_STATUS)">{{runner.UF_STATUS_ORIGINAL.TITLE}} с {{runner.UF_CREATE_DATE_ORIGINAL.FORMAT1}}</div>
                <changestatus :catalog="runner.STATUSLIST" :tindex="runnerindex" v-model="runner.UF_STATUS"/>

                <div class="history-change-block mt-4" v-if="runner.UF_HISTORYCHANGE_ORIGINAL.length>0">
                    <input :id="$id('historystatus'+runner.ID)" type="checkbox" @change="showhidehistory">
                    <label class="btn btn-primary" :for="$id('historystatus'+runner.ID)">История статусов</label>
                    <div class="history-list mt-3 p-3">
                        <div class="mb-2" v-for="status_history in runner.UF_HISTORYCHANGE_ORIGINAL">
                                <b>{{status_history.DATE_CHANGE}}</b>
                                пользователь {{status_history.USER_CHANGE}} сменил статус с
                                <u>{{status_history.OLD_STATUS_TITLE}} id({{status_history.OLD_STATUS_ID}})</u>
                                на
                                <u>{{status_history.NEW_STATUS_TITLE}} id({{status_history.NEW_STATUS_ID}})</u>
                        </div>
                    </div>
                </div>

        </td>


        <td style="width: 20%">
            <div class="mb-3 form-group datepicker-input">
                <div><label class="" for="factdate-execution">Дата публикации</label></div>
                <div class="d-flex">
                    <mydatepicker :tindex="runnerindex" :original="runner.UF_ACTUAL_DATE_ORIGINAL.FORMAT1" :mindd="runner.UF_ACTUAL_DATE_ORIGINAL.MINDATE" v-model="runner.UF_ACTUAL_DATE"/>
                </div>
            </div>

            <accountfield :tindex="runnerindex" v-model="runner.UF_SITE_SETUP"/>

            <div class="p-3 report-block" v-if="isViewReport(runner.UF_STATUS)">
                <div class="h4" style="margin-top: 0;">Отчет по исполнению</div>
                <div class="form-group">
                        <label class="col-form-label" for="contract_type" style="padding-bottom: 0;">Ссылка:</label>
                        <input type="text" class="form-control" v-model="runner.UF_REPORT_LINK" @change="inpsave(runnerindex)">
                </div>
                <div class="form-group">
                        <label class="col-form-label" for="contract_type" style="padding-bottom: 0;">Скриншот отчета:</label>
                        <input type="text" class="form-control" v-model="runner.UF_REPORT_SCREEN" @change="inpsave(runnerindex)">
                </div>

                <div class="form-group">
                        <label class="col-form-label" for="contract_type" style="padding-bottom: 0;">Файл отчета:</label>
                        <input type="text" class="form-control" v-model="runner.UF_REPORT_FILE" @change="inpsave(runnerindex)">
                </div>

                <div class="form-group">
                        <label class="col-form-label" for="contract_type" style="padding-bottom: 0;">Текст отчета:</label>
                        <textarea class="form-control" v-model="runner.UF_REPORT_TEXT" @change="inpsave(runnerindex)"></textarea>
                </div>
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
</script>

<?
$client_state = CUtil::PhpToJSObject($arResult["CLIENT_DATA"], false, true);
$project_state = CUtil::PhpToJSObject($arResult["PROJECT_DATA"], false, true);
$task_state = CUtil::PhpToJSObject($arResult["TASK_DATA"], false, true);
$order_state = CUtil::PhpToJSObject($arResult["ORDER_DATA"], false, true);
$runner_state = CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true);
$message_state = CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true);
$filter1 = CUtil::PhpToJSObject($arParams["FILTER"], false, true);

(\KContainer::getInstance())->get('catalogStore');
?>
<?ob_start();?>
<script>
    const clientListStoreData = <?=$client_state?>;
	const projectListStoreData = <?=$project_state?>;
	const taskListStoreData = <?=$task_state?>;
	const orderListStoreData = <?=$order_state?>;
	const runnerListStoreData = <?=$runner_state?>;
	const filterclientlist = <?=$filter1?>;
	
	const  messageStore = BX.Vue3.Pinia.defineStore('messagelist', {
    state: () => ({datamessage:<?=$message_state?>}),
    });
	
</script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/assets/js/kabinet/vue-componets/datepicker.js"></script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/assets/js/kabinet/vue-componets/typeahead.js"></script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/assets/js/kabinet/vue-componets/sharephoto.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminclient.data.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminproject.data.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/admintask.data.js"></script>
	<script type="text/javascript" src="<?=$templateFolder?>/adminorder.data.js"></script>
	<script type="text/javascript" src="<?=$templateFolder?>/adminrunner.data.js"></script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/components/exi/profile.user/admin/user.data.php"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminexecution_list.js"></script>

    <script>
	    var messangerperformances = null;

	    // Заглушка, т.к. используется в клиентской части
        const  brieflistStore = BX.Vue3.Pinia.defineStore('brieflist', {
            state: () => ({data:[]}),
        });

        window.addEventListener("components:ready", function(event) {
				messangerperformances = messanger_vuecomponent.start(<?=CUtil::PhpToJSObject([
                    'VIEW_COUNT' => $arParams['MESSAGE_COUNT'],
                ], false, true)?>);

            adminexecution_list.start(<?=CUtil::PhpToJSObject([
						"viewcount"=>$arParams["COUNT"],
					    "total"=>$arResult["TOTAL"]
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