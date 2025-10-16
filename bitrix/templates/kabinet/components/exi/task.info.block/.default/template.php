<?
use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Согласование и отчеты по задаче");


//$APPLICATION->AddChainItem("екнекгне", "/kabinet/projects/?id=28");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$user_id = $user->get('ID');

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$ClientManager = $sL->get('Kabinet.Client');
$projectManager = $sL->get('Kabinet.Project');
$taskManager = $sL->get('Kabinet.Task');
$runnerManager = $sL->get('Kabinet.Runner');

$task_id = $request->get('t');

$taskdata = $taskManager->getData();
$key = array_search($task_id, array_column($taskdata, 'ID'));
if ($key !== false){
    $taskdata = $taskdata[$key];
}
else{
    // throw new \Bitrix\Main\SystemException("Task data not found". "(".$task['ID'].")");
    ShowError("Task data not found". "(".$taskdata['ID'].")");
}

$QueueStatistics = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('queue.statistics')->getStatistics($taskdata);
$runner = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner')->getTaskFulfiData($taskdata['ID']);

$project_data = $projectManager->getData();
$key = array_search($taskdata['UF_PROJECT_ID'], array_column($project_data, 'ID'));
if ($key === false) ShowError("Project data not found". "(".$taskdata['UF_PROJECT_ID'].")");
$project = $project_data[$key];

$user_order = $projectManager->orderData();
$user_order = $user_order[$project['UF_ORDER_ID']][$taskdata['UF_PRODUKT_ID']];

//\Dbg::print_r($taskdata);
?>
<div id="taskinfocontent" class="col-md-12" data-taskinfo=""></div>

<script type="text/html" id="task-info-template">
    <div id="task<?=$taskdata['ID']?>" class="panel">
        <div class="panel-body">

            <div class="row">
                <div class="col-md-1">
                    <img src="<?=$user_order['PREVIEW_PICTURE_SRC']?>" alt="<?=$taskdata['UF_NAME']?>">
                </div>
                <div class="col-md-8">
                    <div class="h3" style="margin-top:0px;"><?=$taskdata['UF_NAME']?> #<?=$taskdata['UF_EXT_KEY']?></div>

                    <div class="d-flex task-status-print h4" v-html="taskStatus_m(TASK_ID)"></div>

                    <div class="mt-3">
                        <div class="d-flex no-d-flex">
                            <div class="d-flex mr-3 align-items-center">Запланированы: <div class="fc-event-light ml-2 mr-2"><?=$QueueStatistics[0]['COUNT']?></div></div>
                            <div class="d-flex mr-3 align-items-center">Выполняются: <div class="fc-event-success ml-2 mr-2"><?=$QueueStatistics[1]['COUNT']?></div></div>
                            <div class="d-flex mr-3 align-items-center">Выполнено: <div class="fc-event-warning ml-2 mr-2"><?=$QueueStatistics[2]['COUNT']?></div></div>
                        </div>
                        <div>Примерная частота исполнений: 1 ед. <?=\PHelp::dimensiontimeConvert($user_order['MINIMUM_INTERVAL']['VALUE'])?></div>
                        <div>Завершится: <?=$taskdata['UF_DATE_COMPLETION_ORIGINAL']['FORMAT1']?></div>
                        <div class="d-flex link-block" v-if="hasLinks">
                            <div class="mr-4">Ссылка:</div>
                            <div class="link-block-value">
                                <div v-for="(linksite, index) in currentLinks" :key="index">
                                    <a :href="linksite.VALUE" target="_blank" rel="nofollow">{{ linksite.VALUE }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <?
                        $key = array_search($taskdata['UF_CYCLICALITY'], array_column($taskdata['UF_CYCLICALITY_ORIGINAL'], 'ID'));
                        if ($key !== false){
                            ?>
                            <?=$taskdata['UF_CYCLICALITY_ORIGINAL'][$key]['VALUE']?>
                        <?}?>
                    </div>

                    <!-- ПЕРЕНЕСЕННЫЙ БЛОК: Дополните задачу данными -->
                    <div class="mt-4">
                        <div class="h4"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;Дополните задачу данными:</div>

                        <div class="form-group d-flex align-items-center mobile-view">
                            <label class="col-form-label col-form-label-custom lable-link-list-style-1" :for="'linkInputLink'+TASK_ID">Ссылка:</label>
                            <div class="target-list-link-block">
                                <div v-for="(inplist, index) in taskData.UF_TARGET_SITE" :key="index">
                                    <input
                                            :class="['form-control', { 'it-required_field': isRequiredField(taskData, 'UF_TARGET_SITE') },'link_input']"
                                            :id="'linkInputLink'+TASK_ID+index"
                                            type="text"
                                            placeholder="https://site.ru"
                                            v-model="inplist.VALUE"
                                            @input="onInputChange"
                                    >
                                </div>
                                <div class="" style="position: relative;">
                                    <button class="text-button" type="button" @click="addMoreLink">+ еще ссылка</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-flex align-items-center mobile-view" style="margin-top: 7px;" v-if="productData.JUST_FILED && productData.JUST_FILED.VALUE">
                            <label class="col-form-label col-form-label-custom" :for="'justfieldInput'+TASK_ID">{{productData.JUST_FILED.VALUE}}:</label>
                            <input
                                    class="form-control"
                                    :id="'justfieldInput'+TASK_ID"
                                    type="text"
                                    placeholder=""
                                    v-model="taskData.UF_JUSTFIELD"
                                    @input="onInputChange"
                            >
                        </div>

                        <div class="form-group d-flex align-items-center mobile-view" v-if="productData.PHOTO_AVAILABILITY && productData.PHOTO_AVAILABILITY.VALUE_XML_ID != '<?=\Bitrix\Kabinet\task\Taskmanager::PHOTO_NO_NEEDED?>'">
                            <label class="col-form-label col-form-label-custom" :for="'InputPhoto'+TASK_ID">Фото:</label>
                            <div id="previewfileimages" class="d-flex flex-wrap">
                                <div class="preview-img-block-1" v-for="photo in limitedPhotos" :style="'background-image:url('+photo.SRC+')'" :key="photo.ID">
                                    <div @click="removePhoto(photo.ID)" class="remove-preview-image"><i class="fa fa-times" aria-hidden="true"></i></div>
                                </div>

                                <div class="preview-img-block-1" v-if="taskData.UF_PHOTO_ORIGINAL.length==0">
                                    <img src="/bitrix/templates/kabinet/assets/images/product.noimage.png" alt="" style="width: 150px;">
                                </div>
                                <div class="preview-img-block-1 d-flex justify-content-center align-items-center" v-if="taskData.UF_PHOTO_ORIGINAL.length > photoLimit && showAllPhotos === false">
                                    <button class="btn btn-secondary show-all-butt" type="button" @click="showAllPhotos = true">показать все {{taskData.UF_PHOTO_ORIGINAL.length}}</button>
                                </div>
                                <div class="preview-img-block-1 addbutton d-flex justify-content-center align-items-center">
                                    <div class="text-center">
                                        <span class="add-images-marker-1"><i class="fa fa-cloud-download" aria-hidden="true"></i></span>
                                        <div style="position: absolute;bottom: 0;left: 27%;font-size: 12px;">Всего: {{taskData.UF_PHOTO_ORIGINAL.length}}</div>
                                    </div>
                                    <input type="file" @change="onPhotoChange" multiple/>
                                </div>
                            </div>
                        </div>


                        <div class="form-group d-flex align-items-center mobile-view" v-if="productData.COORDINATION && productData.COORDINATION.VALUE_XML_ID == '<?=\Bitrix\Kabinet\task\Taskmanager::IS_SOGLACOVANIE?>'">
                            <label class="col-form-label col-form-label-custom" :for="'linkInputSoglacovanie'+TASK_ID">Согласование:</label>
                            <select class="form-control desktop-width" :id="'linkInputSoglacovanie'+TASK_ID" v-model="taskData.UF_COORDINATION" @change="onInputChange">
                                <option v-for="option in filteredCoordinationOptions" :value="option.ID">{{ option.VALUE }}</option>
                            </select>
                        </div>

                        <div class="form-group d-flex align-items-center mobile-view">
                            <label class="col-form-label col-form-label-custom" :for="'linkInputReporting'+TASK_ID">Отчетность:</label>
                            <select class="form-control desktop-width" :id="'linkInputReporting'+TASK_ID" v-model="taskData.UF_REPORTING" @change="onInputChange">
                                <option v-for="option in filteredReportingOptions" :value="option.ID">{{ option.VALUE }}</option>
                            </select>
                        </div>


                        <div class="row form-group mt-3">
                            <div class="col-sm-10 offset-sm-2" style="position: relative;">
                                <button class="btn btn-primary mr-3" type="button" @click="saveTaskData" :disabled="!hasChanges">Сохранить</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
                    <ul class="list-unstyled">
                        <li><a href="/kabinet/projects/planning/?p=<?=$project['ID']?>#produkt<?=$taskdata['UF_PRODUKT_ID']?>">Планирование</a></li>
                        <li><a href="/kabinet/projects/breif/?id=<?=$project['ID']?>">Редактировать бриф</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</script>

<script>
    components.tasklist22 = {
        selector: '[data-taskinfo]',
        script: [
            "../../kabinet/components/exi/reports.list/.default/js/task.info.js",
            '../../kabinet/components/exi/task.list/.default/task_status.js'
        ],
        init:null
    }

    window.addEventListener("components:ready", function(event) {

        const PHPPARAMS = <?=CUtil::PhpToJSObject([
            'TASK_ID' => $task_id
        ], false, true)?>;

        const taskinfoApplication = BX.Vue3.BitrixVue.createApp(taskinfoApplicationConfig);
        configureVueApp(taskinfoApplication,"#taskinfocontent");
    });
</script>