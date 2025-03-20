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


Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>


<div id="kabinetcontent" data-datetimepicker="" data-loadtable="" data-modalload=""></div>

<script type="text/html" id="kabinet-content">
    <div class="panel">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            </div>
        </div>

        <div class="panel-body">

            <table class="table" style="width: 100%;"><tr><td width="10%">Клиент</td><td style="width: 28%;">Проекты</td><td style="width: 44%;">Задачи</td><td style="width: 20%;">Исполнения</td></tr></table>

<table class="table">

    <tbody>
    <tr v-for="(client,clientindex) in dataclient">
        <td style="border-right: 1px solid #dde3e8;width: 10%;padding: 0;">
            <div>
                <div class="h4">{{client.NAME}} <span class="badge badge-warning"># {{client.ID}}</span></div>
				<div class="">E-mail: <a :href="'mailto:'+client.EMAIL">{{client.EMAIL}}</a></div>
			</div>
			<div class="mt-4">
				<div class="font-weight-bold">Кабинет клиента:</div>
				<ul class="list-unstyled">
					<li><a :href="'/kabinet/profile/?usr='+client.ID" target="_blank">Профиль <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/?usr='+client.ID" target="_blank">Дашборд и проекты <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/finance/?usr='+client.ID" target="_blank">Финансы <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
				</ul>
			</div>
        </td>
        <td style="padding: 0;">

            <table class="table table-borderless">
                <tr v-for="project in dataproject[client.ID]" style="">
                    <td style="border-right: 1px solid #dde3e8;border-bottom: 1px solid #dde3e8;width: 32%;padding: 0;">
                        <div style="padding: 10px;">
                                <div :id="'project-title-id-'+project.ID" class="font-weight-bold h4" style="margin-top: 0;">{{project.UF_NAME}}</div>
                                <div class="font-weight-bold">Кабинет клиента:</div>
                                <ul class="list-unstyled">
                                    <li><a :href="'/kabinet/projects/breif/?id='+project.ID+'&usr='+client.ID" target="_blank">Бриф <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                                    <li><a :href="'/kabinet/projects/planning/?p='+project.ID+'&usr='+client.ID" target="_blank">Планирование задач <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                                </ul>
                                <div v-for="task in datatask[client.ID]">
                                    <div v-if="project.ID == task.UF_PROJECT_ID">
                                        <a :href="'/kabinet/projects/reports/?t='+task.ID+'&usr='+client.ID" target="_blank">Согласование и отчеты {{task.UF_NAME}} <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                        </div>
                    </td>
                    <td style="padding: 0;">
                        <?/*
                                '!UF_STATUS'=>[0,9,10]
                        */?>
                        <div v-if="typeof datatask[client.ID] == 'undefined'">У клиента еще нет задач</div>
                            {{(count_task=0,null)}}
                            <div v-for="task in datatask[client.ID]">
                                <table v-if="project.ID == task.UF_PROJECT_ID" style="width: 100%">
                                    {{(count_task=count_task+1,null)}}
                                    <tr>
                                        <td style="border-right: 1px solid #dde3e8;border-bottom: 1px solid #dde3e8;width: 70%;padding: 0;">
                                            <div style="padding: 10px;">
                                            {{badTask(task.ID,client.ID,project.UF_ORDER_ID,task.UF_PRODUKT_ID)}}
                                            <div class="d-flex" v-if="typeof dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID] !='undefined'">
                                                <div><img :src="dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID].PREVIEW_PICTURE_SRC"></div>
                                                <div class="ml-3">
                                                    <form action="/kabinet/admin/performances/" method="post" target="_blank">
                                                        <!-- устанавливаем фильтр -->
                                                        <input type="hidden" name="clientidsearch" :value="client.ID">
                                                        <input type="hidden" name="projectidsearch" :value="project.ID">
                                                        <input type="hidden" name="taskidsearch" :value="task.ID">
                                                        <button :id="'task'+task.ID" class="project-go-1" type="submit">{{task.UF_NAME}}</button>
                                                    </form>


                                                    <div class="">Стоимость: <span style="font-size: 23px;">{{task.FINALE_PRICE}} <span v-if="task.UF_CYCLICALITY == 2">руб./месяц</span><span v-if="task.UF_CYCLICALITY != 2">руб.</span></span></div>
                                                    <div class="info-blk">Дата создания: <span>{{task.UF_PUBLISH_DATE_ORIGINAL.FORMAT1}}</span></div>
                                                    <div class="info-blk">Дата завершения: <span>{{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</span></div>
                                                    <div class="info-blk">Согласование: <span>{{viewListFieldTitle(task,'UF_COORDINATION')}}</span></div>
                                                    <div class="info-blk">Отчетность: <span>{{viewListFieldTitle(task,'UF_REPORTING')}}</span></div>
                                                    <div class="info-blk">Тип процесса: <span>{{viewListFieldTitle(task,'UF_CYCLICALITY')}}</span></div>
                                                </div>
                                            </div>
                                            </div>
                                        </td>
                                        <td style="border-bottom: 1px solid #dde3e8;padding: 0;">
                                            <div style="padding: 10px;">

                                                <input type="hidden" name="clientidsearch" :value="client.ID">
                                                <div class="form-group select-status" v-for="(TitleStatus,idStatus) in statusCatalog()">
                                                    <div class="form-check" v-if="getExecutionStatusCount2(client.ID,task.ID,idStatus)>0">
                                                        <form action="/kabinet/admin/performances/" method="post" target="_blank">
                                                        <!-- устанавливаем фильтр -->
                                                        <input type="hidden" name="clientidsearch" :value="client.ID">
                                                        <input type="hidden" name="projectidsearch" :value="project.ID">
                                                        <input type="hidden" name="taskidsearch" :value="task.ID">

                                                        <input @change="gotocearchstatus" name="statusexecutionsearch" class="form-check-input" :id="'project'+project.ID+'tast'+task.ID+$id(idStatus)" type="radio" :value="idStatus">
                                                        <label class="form-check-label text-primary" :for="'project'+project.ID+'tast'+task.ID+$id(idStatus)">{{TitleStatus}} - <span class="badge badge-secondary">{{getExecutionStatusCount2(client.ID,task.ID,idStatus)}}</span></label>
                                                        </form>
                                                    </div>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        <table v-if="count_task == 0" style="width: 100%;height: 200px">
                            <tr>
                                <td style="border-right: 1px solid #dde3e8;border-bottom: 1px solid #dde3e8;width: 100%;padding: 0;">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

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
$filter1 = CUtil::PhpToJSObject($arParams["FILTER"], false, true);
?>
<?ob_start();?>
<script>
    const clientListStoreData = <?=$client_state?>;
	const projectListStoreData = <?=$project_state?>;
	const taskListStoreData = <?=$task_state?>;
	const orderListStoreData = <?=$order_state?>;
	const runnerListStoreData = <?=$runner_state?>;
	const filterclientlist = <?=$filter1?>;
</script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminclient.data.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminproject.data.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/admintask.data.js"></script>
	<script type="text/javascript" src="<?=$templateFolder?>/adminorder.data.js"></script>
	<script type="text/javascript" src="<?=$templateFolder?>/adminrunner.data.js"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/adminclient_list.js"></script>

    <script>
        adminclient_list.start(<?=CUtil::PhpToJSObject([
                "viewcount"=>$arParams["COUNT"],
            "total"=>$arResult["TOTAL"],
            "statuslistdata" => $runnerManager->getStatusList(),
        ], false, true)?>);
    </script>
<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>