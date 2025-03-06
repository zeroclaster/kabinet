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

//echo "<pre>";
//print_R($arResult['ITEMS']);
//echo "</pre>";



CUtil::InitJSCore(array('window'));

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$p = $request->get('p');
?>

<div id="kabinetcontent" class="form-group" data-datetimepicker="" data-modalload="" data-ckeditor=""  data-usertasklist="" data-tasklist=""></div>

<script type="text/html" id="kabinet-content">

    <div class="panel project-item-block mb-5">
        <div class="panel-body">
            <div class="d-flex flex-wrap">
                <div v-for="task in projectTask(project_id)" class="order-item-block">
                    {{(order = data2[projectOrder(project_id)][task['UF_PRODUKT_ID']],null)}}

                    <img class="img-thumbnail mt-0" :src="order['PREVIEW_PICTURE_SRC']" :alt="order['NAME']" @click="viewTask(task.ID)">

                    <?/*
                    <img class="img-thumbnail mt-0" :src="order['PREVIEW_PICTURE_SRC']" :alt="order['NAME']" @click="viewTask(data2[projectOrder(project_id)][task['UF_PRODUKT_ID']].ID)">
                    */?>

                    <?/*<div class="block-remove-butt"><button class="order-remove-button" type="button" @click="removeProductModal(PRODUKT)"><i class="fa fa-times" aria-hidden="true"></i></button></div>
                    */?>
                </div>

                <button type="button" class="add-butt-order" @click="addbuttorder(project)"></button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" :id="'exampleModal'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel">{{modaldata.title}}</h3>
                </div>
                <div class="modal-body">

                    <div class="row mb-4">
                        <div class="col-auto" style="width: 50%">
                        <input ref="inputclearsearch" class="form-control" type="text" placeholder="начните вводить название услуги..." @input="searchfilter1">
                        </div>
                        <div class="col-auto">
                            <button ref="buttonclearsearch" type="button" class="btn btn-primary" style="display: none;" @click="clearsearchinput">Очистить</button>
                        </div>
                    </div>
                    <div style="overflow:visible;height: 400px;">
                        <div v-for="product in listprd" class="d-flex justify-content-between mb-3">
                            <div><a :href="product.LINK" target="_blank"><img class="img-thumbnail" :src="product['PREVIEW_PICTURE_SRC']" :alt="product['NAME']" style="width: 65px;"></a></div>
                            <div class="align-self-center" style="width: 50%;"><a :href="product.LINK" target="_blank">{{product.NAME}}</a></div>
                            <div class="align-self-center">{{product.PRICE}}</div>

                            <?/*
                            <div class="align-self-center count-button-change"><button class="btn btn-warning plus-btn" type="button" @click="increment(product)">+</button><input type="text" class="count-product-input" v-model="product.COUNT"><button class="btn btn-warning minus-btn" type="button" @click="decrease(product)">-</button></div>
                            */?>



                            <div class="align-self-center">
                                <button class="btn btn-block btn-sm btn-info" type="button" @click="chooseadd(product)">ДОБАВИТЬ</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" :id="'exampleModal2'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel">{{modal2data.title}}</h3>
                </div>
                <div class="modal-body">
                    <div v-if="modal2data.message == ''">{{modal2data.question}}</div>
                    <div>{{modal2data.message}}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="removeproduct(modal2data.basketitem,modal2data.order_id)">Удалить</button>
                    <button type="button" class="btn btn-secondary" @click="closemodal2">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div v-for="(task,taskindex) in datatask">
    <div :id="'produkt'+task.ID" class="panel task-list-block1 mb-5" v-if="task.UF_PROJECT_ID == project_id">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-1">
                    {{(PRODUCT=data2[projectOrder(task.UF_PROJECT_ID)][task.UF_PRODUKT_ID],null)}}
                    <img class="img-thumbnail mt-0" :src="PRODUCT['PREVIEW_PICTURE_SRC']" :alt="PRODUCT['NAME']">
                </div>
                <div class="col-md-9">
                    <div class="h3 task-title-view" :id="'task'+task.ID">{{task.UF_NAME}}</div>
					<?/*
					for debug
                    {{PRODUCT}}
					*/?>
					<div class="d-flex task-status-print" v-html="taskStatus(taskindex)"></div>
                    <div class="price-product" v-if="PRODUCT.CATALOG_PRICE_1>0">Цена за ед.: <span>{{PRODUCT.CATALOG_PRICE_1}}</span> <span>руб.</span></div>
                    <div class="price-product" v-if="PRODUCT.CATALOG_PRICE_1==0">Цена за ед.: <span>по запросу</span></div>


                    <div v-if="task.UF_CYCLICALITY == 1 && task.UF_STATUS>0">
                        Задача выполняется и будет завершена {{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}. Вы можете продлить выполнение задачи после {{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}, указав новую дату завершения и заказав новые исполнения.
                    </div>

                    <div v-if="task.UF_CYCLICALITY == 2 && task.UF_STATUS>0">
                        {{(NEXTMOUTHSTART=2+2,null)}}
                        Задача выполняется. Вы можете изменить ежемесячное количество и другие параметры задачи. Изменения вступят в силу с 1 числа следующего месяца — с {{dateStartNextMounth().format('DD.MM.YYYY')}}.
                    </div>


					<div class="">
							<?/*
                            14.02.2025 сохранение поля Ссылка было на событии @change="savetask(taskindex)"
                            14.02.2025 добавили кнопку сохранить
                            */?>
                            <div class="row form-group">
								<div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
									<label class="col-form-label" for="linkInput1">Ссылка:</label>
								</div>

								<div class="col-sm-6" style="position: relative;">
									<div class="mt-3" v-for="inplist in task.UF_TARGET_SITE">
                                    <input class="form-control" id="linkInput1" type="text" placeholder="https://site.ru" v-model="inplist.VALUE">
                                    </div>
                                    <div class="" style="position: relative;">
                                        <button class="text-button" type="button" @click="addmoreinput(task)">+ еще ссылка</button>
                                    </div>
                                </div>
							</div>

                        <div class="row form-group" style="margin-top: 7px;" v-if="PRODUCT.JUST_FILED.VALUE">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="justfieldInput">{{PRODUCT.JUST_FILED.VALUE}}:</label>
                            </div>
                            <div class="col-sm-6" style="position: relative;">
                                    <input class="form-control" id="justfieldInput" type="text" placeholder="" v-model="task.UF_JUSTFIELD">
                            </div>
                        </div>
							
							<div class="row form-group"  v-if="PRODUCT.PHOTO_AVAILABILITY.VALUE_XML_ID != '<?=\Bitrix\Kabinet\task\Taskmanager::PHOTO_NO_NEEDED?>'">
								<div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
									<label class="col-form-label" for="linkInput3">Фото:</label>
								</div>
								<div class="col-sm-10" style="position: relative;">
									<div id="previewfileimages" class="d-flex flex-wrap">
												<div class="preview-img-block-1" v-for="photo in showpiclimits(task.UF_PHOTO_ORIGINAL,taskindex)" :style="'background-image:url('+photo.SRC+')'">
														<div @click="removeimg(photo.ID,taskindex)" class="remove-preview-image"><i class="fa fa-times" aria-hidden="true"></i></div>
												</div>

												<div class="preview-img-block-1" v-if="task.UF_PHOTO_ORIGINAL.length==0"><img src="/bitrix/templates/kabinet/assets/images/product.noimage.png" alt="" style="width: 150px;"></div>
                                        <div class="preview-img-block-1 d-flex justify-content-center align-items-center" v-if="task.UF_PHOTO_ORIGINAL.length>limitpics && task.LIMIT==limitpics">
                                            <button class="btn btn-secondary show-all-butt" type="button" @click="showall(task)">показать все {{task.UF_PHOTO_ORIGINAL.length}}</button>
                                        </div>
                                        <myInputFileComponent :tindex="taskindex" v-model="task.UF_PHOTO" />
									</div>
								</div>
							</div>

                        <?/*
                            Проверяем есть ли согласование у услуги из каталога PRODUCT.COORDINATION.VALUE_XML_ID
                        */?>
                        <div class="row form-group" v-if="PRODUCT.COORDINATION.VALUE_XML_ID == '<?=\Bitrix\Kabinet\task\Taskmanager::IS_SOGLACOVANIE?>'">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="linkInput70">Согласование:</label>
                            </div>

                            <div class="col-sm-6" style="position: relative;">
                                    <select class="form-control" name="" id="" v-model="task.UF_COORDINATION">
                                        <option v-for="option in clearFirstItem(task.UF_COORDINATION_ORIGINAL)" :value="option.ID">
                                            {{ option.VALUE }}
                                        </option>
                                    </select>
                            </div>
                        </div>

                        <div class="row form-group">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="linkInput4">Отчетность:</label>
                            </div>

                            <div class="col-sm-6" style="position: relative;">
                                    <select class="form-control" name="" id="" v-model="task.UF_REPORTING">
                                        <option v-for="option in clearFirstItem(task.UF_REPORTING_ORIGINAL)" :value="option.ID">
                                            {{ option.VALUE }}
                                        </option>
                                    </select>
                            </div>
                        </div>


                        <div class="row form-group" v-if="(task.UF_CYCLICALITY == 1 || task.UF_CYCLICALITY == 2) && task.UF_STATUS==0">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="kolichestvo" style="padding-top: 0px;">Количество:</label>
                            </div>

                            <div class="col-sm-10" style="position: relative;">
                                <div class="d-flex">
                                        <div>
                                            <input id="kolichestvo" type="text" class="form-control" style="width: 100px;" size="2"  v-model="task.UF_NUMBER_STARTS">
                                        </div>
                                        <div class="ml-3 mr-3 task-text-vertical-aling"> ед.</div>
                                        <div class="mr-3">                                                  
                                            <select class="form-control" name="" id="" v-model="task.UF_CYCLICALITY">
												<option v-for="option in task.UF_CYCLICALITY_ORIGINAL" :value="option.ID">
														{{ option.VALUE }}
												</option>
                                            </select>
											
											<div>Примерная периодичность: 1 ед. в {{frequency(taskindex)}}</div>
        
                                        </div>
                                        <div style="position: relative" v-if="task.UF_CYCLICALITY == 1 && task.UF_DATE_COMPLETION">
                                            <div class="input-group">
                                                <mydatepicker :tindex="taskindex" :original="task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1" :mindd="task.UF_DATE_COMPLETION_ORIGINAL.MINDATE" :maxd="task.UF_DATE_COMPLETION_ORIGINAL.MAXDATE" v-model="task.UF_DATE_COMPLETION"/>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <div class="row form-group" v-if="task.UF_CYCLICALITY == 1 && task.UF_STATUS>0">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="kolichestvo" style="padding-top: 0px;">Количество:</label>
                            </div>

                            <div class="col-sm-10" style="position: relative;">
                                <div class="d-flex">
                                    <div>
                                        <input id="kolichestvo" type="text" class="form-control" style="width: 100px;" size="2"  v-model="task.UF_NUMBER_STARTS">
                                    </div>
                                    <div class="ml-3 mr-3 task-text-vertical-aling"> ед.</div>
                                    <div class="mr-3">
                                        <div style="padding: 14px;padding-left: 0px;">{{ showOne1(task.UF_CYCLICALITY_ORIGINAL) }}</div>
                                        <div>Примерная периодичность: 1 ед. в {{frequency(taskindex)}}</div>
                                    </div>
                                    <div style="position: relative">
                                        <div class="input-group">
                                            <mydatepicker :tindex="taskindex" :original="task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1" :mindd="task.UF_DATE_COMPLETION_ORIGINAL.MINDATE" :maxd="task.UF_DATE_COMPLETION_ORIGINAL.MAXDATE" v-model="task.UF_DATE_COMPLETION"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ID 2 Ежемесечная задача -->
                        <div class="row form-group" v-if="task.UF_CYCLICALITY == 2 && task.UF_STATUS>0">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center">
                                <label class="col-form-label" for="kolichestvo" style="padding-top: 0px;">Изменить количество со следующего месяца:</label>
                            </div>

                            <div class="col-sm-10" style="position: relative;">
                                <div class="d-flex">
                                    <div>
                                        <input id="kolichestvo" type="text" class="form-control" style="width: 100px;" size="2"  v-model="task.UF_NUMBER_STARTS">
                                    </div>
                                    <div class="ml-3 mr-3 task-text-vertical-aling"> ед./в месяц</div>
                                </div>
                                <div>Новое количество применится с {{dateStartNextMounth().format('DD.MM.YYYY')}} Примерная периодичность: 2-3 ед. в день.</div>
                            </div>
                        </div>

                        <!-- ID 33 Одно исполнение -->
                        <div class="row form-group" v-if="task.UF_CYCLICALITY == 33">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center"><label class="col-form-label" style="padding-top: 0px;">Срок исполнения до:</label></div>
                            <div class="col-sm-10" style="position: relative;">{{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</div>
                        </div>

                        <!-- ID 34 Ежемесячная услуга -->
                        <div class="row form-group" v-if="task.UF_CYCLICALITY == 34">
                            <div class="col-sm-2 text-sm-right d-flex justify-content-end align-items-center"></div>
                            <div class="col-sm-10" style="position: relative;">
                                <div>Ежемесячная услуга, ближайшая отчетная дата и дата следующего списания средств: {{task.RUN_DATE}}</div>
                                <div v-if="task.UF_STATUS == 15" style="word-wrap: unset;"><button class="btn btn-link btn-link-site" type="button" style="padding: 0" @click="stoptask(taskindex)">Остановить с {{task.RUN_DATE}}</button></div>
                            </div>
                        </div>


                        <?/*
                            СТОИМОСТЬ
                        */?>
                        <div class="row form-group" v-if="task.FINALE_PRICE>0">
                            <div class="col-sm-2 text-sm-right"><label class="col-form-label" for="linkInput2">Стоимость:</label></div>
                            <div class="col-sm-6" style="position: relative;">
                                <div class="task-text-vertical-aling task-price-total">
                                    <span>{{task.FINALE_PRICE}}</span>
									<span v-if="task.UF_CYCLICALITY==1 || task.UF_CYCLICALITY==33"> руб.</span>
									<span v-if="task.UF_CYCLICALITY==2 || task.UF_CYCLICALITY==34"> руб. (/мес.)</span>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group" v-if="task.FINALE_PRICE==0">
                            <div class="col-sm-2 text-sm-right"><label class="col-form-label" for="linkInput2">Стоимость:</label></div>
                            <div class="col-sm-6" style="position: relative;">
                                <div class="task-text-vertical-aling task-price-total">по запросу</div>
                            </div>
                        </div>
                        
						<div class="row form-group">
                            <div class="col-sm-10 offset-sm-2" style="position: relative;">
                                <div class="d-flex">
                                <button class="btn btn-primary mr-3" type="button" @click="saveButton(taskindex)">Сохранить</button>
								<button :id="'taskbutton1'+task.ID"  v-if="countQueu(taskindex) == 0 && task.UF_CYCLICALITY!=2" class="btn btn-secondary" type="button" @click="starttask(taskindex)" disabled="disabled"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Начать выполнение</button>
                                    <button :id="'taskbutton1'+task.ID"  v-if="countQueu(taskindex) == 0 && task.UF_CYCLICALITY==2" class="btn btn-secondary" type="button" @click="starttask(taskindex)" disabled="disabled"><i class="fa fa-forward" aria-hidden="true"></i>&nbsp;Начать выполнение</button>
                                    <?/*
                                    Возможность допланировать

                                    если не 33 Одно исполнение
                                    если не 34 Ежемесячная услуга
                                */?>
                                <button :id="'taskbutton2'+task.ID"  v-if="countQueu(taskindex) > 0 && task.UF_CYCLICALITY!=33 && task.UF_CYCLICALITY!=34 && task.UF_CYCLICALITY!=2" class="btn btn-secondary" type="button" @click="starttask(taskindex)" disabled="disabled"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Продлить задачу до {{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</button>
                                    <button :id="'taskbutton2'+task.ID"  v-if="countQueu(taskindex) > 0 && task.UF_CYCLICALITY==2" class="btn btn-secondary" type="button" @click="starttask(taskindex)" disabled="disabled"><i class="fa fa-forward" aria-hidden="true"></i>&nbsp;Применить с {{dateStartNextMounth().format('DD.MM.YYYY')}}</button>
                                </div>
                            </div>
						</div>							
							
					</div>
					
                </div>
                <div class="col">
					<ul class="list-unstyled">
						<li v-if="countQueu(taskindex) > 0"><a style="padding-left: 0px;" :href="'/kabinet/projects/reports/?t='+task.ID">Согласование и отчеты <span class="badge badge-iphone-style badge-pill">{{viewTaskAlert(task.ID)}}</span></a></li>
						<li><a style="padding-left: 0px;" :href="'/kabinet/projects/breif/?id='+task.UF_PROJECT_ID">Редактировать бриф</a></li>
						<li v-if="task.UF_STATUS==<?=\Bitrix\Kabinet\task\Taskmanager::WORKED?>"><button class="btn btn-link btn-link-site" type="button" @click="stoptask(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
						<li><button class="btn btn-link btn-link-site" type="button" @click="removetask(taskindex)" style="padding: 0;"><i class="fa fa-trash-o" aria-hidden="true"></i>&nbsp;Удалить в архив</button></li>
                    </ul>
				</div>
            </div>
        </div>
    </div>
    </div>

    <questiona_ctivity_component question="Вы действительно хотите остановить все исполнения задачи?" ref="modalqueststop"/>
    <questiona_ctivity_component question="Вы действительно хотите удалить все исполнения задачи в архив?" ref="modalquestremove"/>
</script>


<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','queueStore');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/task.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/addnewmethods.js");
Asset::getInstance()->addJs($templateFolder."/task_list.js");
?>

<script>
    components.tasklist22 = {
        selector: '[data-tasklist]',
        script: [
            '../../kabinet/components/exi/task.list/.default/scrt.js',
        ],
        init:null
    }


    var questiona_ctivity_component = null;
    window.addEventListener("components:ready", function(event) {

    questiona_ctivity_component = questionactivity_vuecomponent.start(<?=CUtil::PhpToJSObject([], false, true)?>);

    task_list.start(<?=CUtil::PhpToJSObject([
            "PROJECT_ID"=>$arParams["PROJECT"],
            "TASK_ALERT"=>$arResult['TASK_ALERT'],
        ], false, true)?>);
    });
</script>
