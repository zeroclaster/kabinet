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

<div id="kabinetcontent" class="form-group" data-datetimepicker="" data-modalload="" data-ckeditor=""  data-usertasklist="" data-tasklist="" data-fullcalendar2=""></div>

<script type="text/html" id="kabinet-content">

    <div class="panel project-item-block mb-5">
        <div class="panel-body">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <div class="d-flex flex-wrap">
                        <button type="button" class="add-butt-order" @click="addbuttorder(project)"></button>
                    </div>
                </div>
                <div class="col-md-2 block-edit-brief-butt">

                    <template v-if="getRequireFields(project_id).length > 0">
                        <a class="btn btn-danger mdi-alert-outline icon-button text-nowrap" :href="'/kabinet/projects/breif/?id='+project_id"><?=Loc::getMessage('PROJECT_FILL_ALL')?></a>
                    </template>
                    <template v-else>
                        <a class="btn btn-primary text-nowrap" :href="'/kabinet/projects/breif/?id='+project_id"><i class="fa fa-list" aria-hidden="true"></i>&nbsp;<?=Loc::getMessage('PROJECT_FILL_ALL')?></a>
                    </template>

                    </div>
            </div>


        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" :id="'exampleModal'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="w-100 search-modale-block">
                            <div class="">
                                <input ref="inputclearsearch" class="form-control" type="text" placeholder="начните вводить название услуги..." @input="searchfilter1">
                            </div>
                            <div class="clear-inpunt-search">
                                <button ref="buttonclearsearch" type="button" class="" style="display: none;" @click="clearsearchinput">x</button>
                            </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div style="overflow:visible;height: 400px;">
                        <div v-for="product in listprd" class="d-flex justify-content-between mb-3">
                            <div><img class="img-thumbnail" :src="product['PREVIEW_PICTURE_SRC']" :alt="product['NAME']" style="width: 65px;cursor: pointer;" @click="chooseadd(product)"></div>
                            <div class="align-self-center" style="width: 50%;"><a class="text-primary" @click="chooseadd(product)" style="cursor: pointer;">{{product.NAME}}</a> <a :href="product.LINK" target="_blank"><i class="fa fa-window-restore" aria-hidden="true"></i></a></div>
                            <div class="align-self-center">{{product.PRICE}}</div>

                            <?/*
                            <div class="align-self-center count-button-change"><button class="btn btn-warning plus-btn" type="button" @click="increment(product)">+</button><input type="text" class="count-product-input" v-model="product.COUNT"><button class="btn btn-warning minus-btn" type="button" @click="decrease(product)">-</button></div>
                            */?>
                            <div class="align-self-center">
                                <button class="btn btn-block btn-sm btn-info" type="button" @click="chooseadd(product)">ВЫБРАТЬ</button>
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

    <template v-for="(task,taskindex) in datatask">

        {{(CopyTask = getCopyTask(task),null)}}
    <div :id="'produkt'+task.ID" :data-cyclicality="task.UF_CYCLICALITY" :data-status="task.UF_STATUS" class="panel task-list-block1 mb-5" v-if="task.UF_PROJECT_ID == project_id">
        {{(PRODUCT=data2[projectOrder(task.UF_PROJECT_ID)][task.UF_PRODUKT_ID],null)}}

        <div class="panel-body">
            <div class="row">
                <?if(isMobileDevice()):?>
                    <div class="col-md-12">
                        <div class="d-flex">
                            <img class="img-thumbnail mt-0" :src="PRODUCT['PREVIEW_PICTURE_SRC']" :alt="PRODUCT['NAME']" style="width: 80px;">
                            <div
                                    class="h3 task-title-view ml-3"
                                    :id="'task'+task.ID"
                                    :data-element-type="getProductByIndexTask(taskindex).ELEMENT_TYPE.VALUE"
                                    :data-cyclicality="task.UF_CYCLICALITY"
                            >{{task.UF_NAME}} #{{task.UF_EXT_KEY}}</div>
                        </div>
                    </div>
                <?else:?>
                <div class="col-md-1">
                    <img class="img-thumbnail mt-0" :src="PRODUCT['PREVIEW_PICTURE_SRC']" :alt="PRODUCT['NAME']">
                </div>
                <?endif;?>
                <div class="col-md-9">
                    <?if(!isMobileDevice()):?>
                        <div
                                class="h3 task-title-view"
                                :id="'task'+task.ID"
                                :data-element-type="getProductByIndexTask(taskindex).ELEMENT_TYPE.VALUE"
                                :data-cyclicality="task.UF_CYCLICALITY"
                        >{{task.UF_NAME}} #{{task.UF_EXT_KEY}}</div>
                    <?endif;?>

					<div class="d-flex align-items-center task-status-print h4">
                        <div v-html="taskStatus_m(task.ID)"></div>

                        <div class="ml-4" v-if="getEventsByTaskId(task.ID).length > 0">
                            <a class="btn btn-primary new-butt-fa-icon text-nowrap" :href="'/kabinet/projects/reports/?t='+task.ID">
                                <i class="fa fa-line-chart align-middle" aria-hidden="true" style="font-size: 21px;"></i>
                                <span class="butt-fa-icon-text">Ход работы</span>
                                <span class="badge badge-iphone-style badge-pill">{{ $root.PHPPARAMS.TASK_ALERT[task.ID] || '' }}</span>
                            </a>
                        </div>

                    </div>

                    <div class="task-palanning-info d-flex no-d-flex" v-if="task.UF_STATUS>0">
                        <div>Всего: {{taskQueueCount(task.ID)}}</div><div class="ml-3">Запланированы: <span class="task-staus-counter alert-planned">{{anim_counter[task.ID]}}</span></div><div class="ml-3">Выполняются: <span class="task-staus-counter alert-worked">{{taskStatus_v(task.ID)['work']}}</span></div><div class="ml-3">Требуют внимания: <span class="task-staus-counter alert-user-attention">{{taskStatus_v(task.ID)['alert']}}</span></div><div class="ml-3">Выполнено: <span class="task-staus-counter alert-done">{{taskStatus_v(task.ID)['endwork']}}</span></div>
                    </div>

                    <timeLineTask :taskindex="taskindex"/>

                    <template v-if="CopyTask.UF_STATUS>0">
                        <div v-if="CopyTask.UF_CYCLICALITY == 1">Примерная частота исполнений: <span class="text-nowrap">1 {{PRODUCT.MEASURE_NAME}} в {{frequency(taskindex)}}</span></div>
                    </template>

                    <!-- Только для работающих задач -->
                    <template v-if="task.UF_STATUS>0">
                        <div v-if="CopyTask.UF_CYCLICALITY == 1">Завершится: {{task.RUN_DATE}}</div>
                        <div v-if="CopyTask.UF_CYCLICALITY == 2 && task.UF_STATUS == <?=\Bitrix\Kabinet\task\Taskmanager::STOPPED?>">Завершится: {{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</div>
                        <!-- Одно исполнение -->
                        <div v-if="CopyTask.UF_CYCLICALITY == 33">Завершится: {{task.RUN_DATE}}</div>
                        <!-- Ежемесячная услуга -->
                        <div v-if="CopyTask.UF_CYCLICALITY == 34">Непрерывная задача</div>
                    </template>


					<div class="">
                        <textInfoTask :task="datatask" :copyTask="datataskCopy" :taskindex="taskindex"/>

                        <template v-if="CopyTask.UF_STATUS==0">
                        <div class="row form-group" v-if="CopyTask.UF_CYCLICALITY == 1 || CopyTask.UF_CYCLICALITY == 2">
                            <div class="col-sm-4 text-sm-right d-flex align-items-center mobile-view">
                                <label class="col-form-label col-form-label-custom col-form-label-mobile" :for="'kolichestvo'+task.ID" style="padding-top: 0px;">Количество:</label>
                                <div class="d-flex align-items-center">
                                    <div class="numerator-range">
                                        <input :id="'kolichestvo'+task.ID" type="text" class="form-control" style="margin: 0;width: 100px;margin-right: 20px;" size="2"  v-model="datataskCopy[taskindex].UF_NUMBER_STARTS" @input="inpsaveCopy(taskindex)">
                                        <button type="button" class="button-plus" @click="datataskCopy[taskindex].UF_NUMBER_STARTS++;inpsaveCopy(taskindex)">+</button>
                                        <button type="button" class="button-minus" @click="decreaseNumberStarts(taskindex)">-</button>
                                    </div>
                                    <div>{{PRODUCT.MEASURE_NAME}}</div>
                                </div>
                            </div>
                            <div class="col-md-4 mr-3 d-flex justify-content-end align-items-center mobile-mt-4" style="padding-right:0;">
                                <select class="form-control" name="" id="" v-model="datataskCopy[taskindex].UF_CYCLICALITY" @change_="inpsaveCopy(taskindex)">
                                    <option v-for="option in CopyTask.UF_CYCLICALITY_ORIGINAL" :value="option.ID">
                                        {{ option.VALUE }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex justify-content-end align-items-center mobile-mt-4" style="position: relative;" v-if="CopyTask.UF_CYCLICALITY == 1 && CopyTask.UF_DATE_COMPLETION">
                                <div class="input-group">
                                    <mydatepicker
                                            :tindex="taskindex"
                                            :original="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.FORMAT1"
                                            :mindd="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.MINDATE"
                                            :maxd="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.MAXDATE"
                                            v-model="datataskCopy[taskindex].UF_DATE_COMPLETION"
                                    />
                                </div>
                            </div>
                        </div>

                            <template v-if="getProductByIndexTask(taskindex).ELEMENT_TYPE.VALUE=='multiple'">
                            <div class="row form-group" v-if="CopyTask.UF_CYCLICALITY == 33">
                                <div class="col-sm-4 text-sm-right d-flex align-items-center mobile-view">
                                    <label class="col-form-label col-form-label-custom col-form-label-mobile" :for="'kolichestvo'+task.ID" style="padding-top: 0px;">Количество:</label>
                                    <div class="d-flex align-items-center">
                                        <div class="numerator-range">
                                        <input
                                                :id="'kolichestvo'+task.ID"
                                                type="text"
                                                class="form-control"
                                                style="margin: 0;width: 100px;margin-right: 20px;"
                                                size="2"
                                                v-model="datataskCopy[taskindex].UF_NUMBER_STARTS"
                                                @input="inpsaveCopy(taskindex)"
                                        >
                                            <button type="button" class="button-plus" @click="datataskCopy[taskindex].UF_NUMBER_STARTS++;inpsaveCopy(taskindex)">+</button>
                                            <button type="button" class="button-minus" @click="decreaseNumberStarts(taskindex)">-</button>
                                        </div>
                                    </div>
                                    <div>{{PRODUCT.MEASURE_NAME}}</div>
                                </div>
                                <div class="col-md-4 mr-3 d-flex justify-content-end align-items-center mobile-mt-4" style="padding-right:0;">
                                    До: {{datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}
                                </div>
                            </div>
                            </template>


                        </template>

                        <div class="row form-group" v-if="CopyTask.UF_CYCLICALITY == 1 && CopyTask.UF_STATUS>0">
                            <div class="col-sm-4 text-sm-right d-flex align-items-center mobile-view">
                                <label class="col-form-label col-form-label-custom col-form-label-mobile" :for="'kolichestvo'+task.ID" style="padding-top: 0px;">Количество:</label>
                                <div class="d-flex align-items-center">
                                    <div class="numerator-range">
                                        <input :id="'kolichestvo'+task.ID" type="text" class="form-control" style="margin: 0;width: 100px;margin-right: 20px;" size="2"  v-model="datataskCopy[taskindex].UF_NUMBER_STARTS" @input="inpsaveCopy(taskindex)">
                                        <button type="button" class="button-plus" @click="datataskCopy[taskindex].UF_NUMBER_STARTS++;inpsaveCopy(taskindex)">+</button>
                                        <button type="button" class="button-minus" @click="decreaseNumberStarts(taskindex)">-</button>
                                    </div>
                                        <div>{{PRODUCT.MEASURE_NAME}}</div>
                                </div>
                            </div>
                            <div class="col-md-4 mr-3 d-flex justify-content-end align-items-center mobile-mt-4" style="padding-right:0;">
                                {{ showOne1(CopyTask.UF_CYCLICALITY_ORIGINAL) }}
                            </div>
                            <div class="col-md-3 d-flex justify-content-end align-items-center mobile-mt-4" style="position: relative;">
                                <div class="input-group">
                                   <mydatepicker
                                            :tindex="taskindex"
                                            :original="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.FORMAT1"
                                            :mindd="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.MINDATE"
                                            :maxd="datataskCopy[taskindex].UF_DATE_COMPLETION_ORIGINAL.MAXDATE"
                                            v-model="datataskCopy[taskindex].UF_DATE_COMPLETION"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ID 2 Ежемесечная задача -->
                        <div class="row form-group" v-if="CopyTask.UF_CYCLICALITY == 2 && CopyTask.UF_STATUS>0">
                            <div class="col-sm-5 text-sm-right d-flex align-items-center mobile-view">
                                <label class="col-form-label col-form-label-custom col-form-label-mobile" :for="'kolichestvo'+task.ID" style="padding-top: 0px;">Изменить количество со следующего месяца:</label>
                                <div class="d-flex align-items-center">
                                    <div class="numerator-range">
                                        <input
                                                :id="'kolichestvo'+task.ID"
                                                type="text"
                                                class="form-control"
                                                style="margin: 0;width: 100px;margin-right: 20px;"
                                                size="2"
                                                v-model="datataskCopy[taskindex].UF_NUMBER_STARTS"
                                                @input="inpsaveCopy(taskindex)"
                                        >
                                        <button type="button" class="button-plus" @click="datataskCopy[taskindex].UF_NUMBER_STARTS++;inpsaveCopy(taskindex)">+</button>
                                        <button type="button" class="button-minus" @click="decreaseNumberStarts(taskindex)">-</button>
                                    </div>
                                    <div>{{PRODUCT.MEASURE_NAME}}/в месяц</div>
                                </div>
                            </div>
                        </div>


                        <?/*
                            СТОИМОСТЬ
                        */?>
                        <div class="row form-group" v-if="CopyTask.FINALE_PRICE>0">
                            <div class="col-lg-4 mobile-col d-flex align-items-center">
                                <label class="col-form-label col-form-label-custom" :for="'linkInput2Price1'+task.ID">Цена за {{PRODUCT.MEASURE_NAME}}: </label>
                                <div class="price-product" style="margin-top: 0px;" v-if="PRODUCT.CATALOG_PRICE_1>0"><span>{{PRODUCT.CATALOG_PRICE_1}}</span> <span>руб.</span></div>
                            </div>

                            <div class="col-sm-4" style="position: relative;">
                                <div class="d-flex">
                                    <div class="text-sm-right"><label class="col-form-label" :for="'linkInput2Price'+task.ID">Стоимость:&nbsp;</label></div>
                                    <div class="task-price-total">
                                        <span>{{CopyTask.FINALE_PRICE}}</span>
                                        <span v-if="CopyTask.UF_CYCLICALITY==1 || CopyTask.UF_CYCLICALITY==33"> руб.</span>
                                        <span v-if="CopyTask.UF_CYCLICALITY==2 || CopyTask.UF_CYCLICALITY==34"> руб/мес</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row form-group" v-if="PRODUCT.CATALOG_PRICE_1==0">
                            <div class="col-sm-2 text-sm-right"><label class="col-form-label" :for="'linkInput2Price1'+task.ID">Цена за {{PRODUCT.MEASURE_NAME}}: </label></div>
                            <div class="col-sm-3" style="position: relative;">
                                <div class="price-product"><span>по запросу</span></div>
                            </div>
                            <div class="col-sm-4" style="position: relative;">
                                <div class="d-flex">
                                    <div class="text-sm-right"><label class="col-form-label" :for="'linkInput2Price'+task.ID">Стоимость:&nbsp;</label></div>
                                    <div class="task-text-vertical-aling task-price-total">по запросу</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row form-group" v-if="ButtoncheckBalance(taskindex)">
                            <div class="col-sm-10 offset-sm-2" style="position: relative;">
                                <div class="d-flex">
                                    <?/* 33 Одно исполнение */?>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length == 0 && CopyTask.UF_CYCLICALITY==33" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Заказать «{{task.UF_NAME}}»</button>

                                    <?/* 1 Однократное выполнение */?>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length == 0 && CopyTask.UF_CYCLICALITY==1" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Заказать {{CopyTask.UF_NUMBER_STARTS}} {{PRODUCT.MEASURE_NAME}} «{{task.UF_NAME}}»</button>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length > 0 && CopyTask.UF_CYCLICALITY==1" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-forward" aria-hidden="true"></i>&nbsp;Заказать ещё {{CopyTask.UF_NUMBER_STARTS}} {{PRODUCT.MEASURE_NAME}} «{{task.UF_NAME}}»</button>

                                    <?/* 2 Повторяется ежемесячно */?>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length == 0 && CopyTask.UF_CYCLICALITY==2" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Заказать {{CopyTask.UF_NUMBER_STARTS}} {{PRODUCT.MEASURE_NAME}} в мес. «{{task.UF_NAME}}»</button>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length > 0 && CopyTask.UF_CYCLICALITY==2" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-forward" aria-hidden="true"></i>&nbsp;Применить {{CopyTask.UF_NUMBER_STARTS}} {{PRODUCT.MEASURE_NAME}} в мес. «{{task.UF_NAME}}» с {{CopyTask.RUN_DATE}}</button>

                                    <?/* 34 Ежемесячная услуга */?>
                                    <button :id="'taskbutton1'+CopyTask.ID"  v-if="getEventsByTaskId(task.ID).length == 0 && CopyTask.UF_CYCLICALITY==34" class="btn btn-secondary" type="button" @click="starttask(taskindex)"><i class="fa fa-step-forward" aria-hidden="true"></i>&nbsp;Заказать «{{task.UF_NAME}}»</button>

                                </div>
                            </div>
                        </div>
                        <div class="row form-group" v-else>
                            <div class="col-sm-10 offset-sm-2" style="position: relative;">
                                <div class="d-flex">
                                    <button :id="'taskbutton1'+CopyTask.ID" class="btn btn-secondary" type="button" @click="starttask(taskindex)">Пополнить балланс</button>
                                </div>
                            </div>
                        </div>

					</div>
					
                </div>
                <div class="col">
					<ul class="list-unstyled task-aciont-list-1">
                        <template v-if="task.UF_STATUS==<?=\Bitrix\Kabinet\task\Taskmanager::WORKED?>">

                                <?/* 1 Однократное выполнение */?>
                                <template v-if="task.UF_CYCLICALITY == 1">
                                    <li><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_1(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
                                </template>

                                <?/* 2 Повторяется ежемесячно */?>
                                <template v-if="task.UF_CYCLICALITY == 2">
                                    <li v-if="taskStatus_v(task.ID)['work'] == 0"><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_2_planned(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
                                    <li v-if="taskStatus_v(task.ID)['work'] > 0"><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_2_worked(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
                                </template>

                                <?/* 33 Одно исполнение */?>
                                <template v-if="task.UF_CYCLICALITY == 33">
                                    <li v-if="taskStatus_v(task.ID)['work'] == 0"><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_33_planned(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>

                                </template>

                                <?/* 34 Ежемесячная услуга */?>
                                <template v-if="task.UF_CYCLICALITY == 34">
                                    <li v-if="taskStatus_v(task.ID)['work'] == 0"><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_34_planned(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
                                    <li v-if="taskStatus_v(task.ID)['work'] > 0"><button class="btn btn-link btn-link-site" type="button" @click="stoptask_cyclicality_34_worked(taskindex)" style="padding: 0;"><i class="fa fa-stop" aria-hidden="true"></i>&nbsp;Остановить</button></li>
                                </template>
                        </template>

                        <li><button class="btn btn-link btn-link-site" type="button" @click="removetask(taskindex)" style="padding: 0;"><i class="fa fa-trash-o" aria-hidden="true"></i>&nbsp;Удалить задачу</button></li>
                    </ul>
				</div>
            </div>
        </div>
    </div>
    </template>

    <!-- Блок общей стоимости проекта -->
    <div class="panel mb-4 total-cost-panel">
        <div class="panel-body">
            <div class="d-flex align-items-center">
                <div class="">
                    <h5 class="mb-0" style="margin-top: 0;">Итого:</h5>
                    <small class="text-muted"></small>
                </div>
                <div class="text-right ml-3">
                    <div class="total-project-cost">
                        <strong>{{ totalProjectCost.toLocaleString('ru-RU') }}</strong> руб.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <questiona_ctivity_component question="Вы действительно хотите отменить задачу? Задачу будет отменена, средства возвращены на баланс." ref="modalqueststopcyclicality33planned"/>
    <questiona_ctivity_component question="Задача выполняется и завершится автоматически, когда будет исполнена. Если вы желаете прервать исполнение задачи – напишите в чат поддержки." ref="modalqueststopcyclicality33worked"/>

    <questiona_ctivity_component question="Задача остановлена, зарезервированные средства будут возвращены на ваш баланс." ref="modalqueststopcyclicality2planned"/>
    <questiona_ctivity_component question="Задача будет выполнена в текущем месяце и далее остановлена." ref="modalqueststopcyclicality2worked"/>


    <questiona_ctivity_component question="Вы хотите остановить выполнение задачи? У задачи есть исполнения в работе, которые будут выполнены по плану. Только неначатые исполнения будут отменены, а средства возвращены на баланс." ref="modalqueststopcyclicality1"/>
    <questiona_ctivity_component question="Задача не может быть остановлена сейчас, так как есть исполнения, взятые в работу. Задача завершится автоматически, когда будет выполнена. Если вы желаете остановить задачу и прервать исполнения – напишите в чат поддержки." ref="modalqueststop"/>
    <questiona_ctivity_component question="Вы действительно хотите удалить эту задачу, все её исполнения и отчеты? Финансовая информация затронута не будет." ref="modalquestremove"/>
</script>


<?php
// В начале PHP файла
$postAction = $_POST['action'] ?? '';
?>

<script>
    // Передаем POST параметры в JavaScript
    window.POST_PARAMS = {
        action: '<?= $postAction ?>'
    };
</script>

<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','queueStore');
?>

<script>
    components.tasklist22 = {
        selector: '[data-tasklist]',
        script: (function() {
            const basePath = './js/kabinet';
            const vueExt = `${basePath}/vue-componets/extension`;
            const taskDef = `../../kabinet/components/exi/task.list/.default`;

            return [
                // Vue components
                ...[
                    'task.js',
                    'addnewmethods.js'
                ].map(file => `${vueExt}/${file}`),

                // Task list components
                ...[
                    'scrt.js',
                    'task_status.js',
                    'canbesaved.js',
                    'js/text_info.js',
                    'data_helper.js',
                    'js/timelinetask2.js',
                    'js/mydatepicker.js',
                    'js/myinputfilecomponent.js',
                    'js/frequencycyclicality.js',
                    'js/frequency.js',
                    'task_list.js',
                    'js/task-calculator.js',
                ].map(file => `${taskDef}/${file}`)
            ];
        })(),
        init: null
    };


    var questiona_ctivity_component = null;
    window.addEventListener("components:ready", function(event) {

    questiona_ctivity_component = questionactivity_vuecomponent.start(<?=CUtil::PhpToJSObject([], false, true)?>);

    task_list.start(<?=CUtil::PhpToJSObject([
            "PROJECT_ID"=>$arParams["PROJECT"],
            "TASK_ALERT"=>$arResult['TASK_ALERT'],
            "ALERT_STATUS"=>\Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner')->config('ALERT'),
        ], false, true)?>);
    });
</script>
