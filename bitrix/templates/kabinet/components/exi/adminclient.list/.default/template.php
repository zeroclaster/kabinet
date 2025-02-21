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
<table class="table">
    <thead>
    <tr>
        <th scope="col">Клиент</th>
        <th scope="col">Проекты</th>
        <th scope="col">Задачи</th>
        <th scope="col">Исполнения</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(client,clientindex) in dataclient">
        <td>
            <div>
				<div class="font-weight-bold">Клиент:</div>
                <div class="text-primary h4">{{client.NAME}} <span class="badge badge-warning"># {{client.ID}}</span></div>
				<div class="">E-mail: <a :href="'mailto:'+client.EMAIL">{{client.EMAIL}}</a></div>
			</div>
			<div class="mt-4">
				<div class="font-weight-bold">Кабинет:</div>
				<ul class="list-unstyled">
					<li><a :href="'/kabinet/profile/?usr='+client.ID">Профиль <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/?usr='+client.ID">Дашборд и проекты <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/finance/?usr='+client.ID">Финансы <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
				</ul>
			</div>
        </td>
        <td>

            <table class="table">
                <tr v-for="project in dataproject[client.ID]">
                    <td>
                        <div>
                                <div class="font-weight-bold h4">{{project.UF_NAME}}</div>
                                <ul class="list-unstyled">
                                    <li><a :href="'/kabinet/projects/breif/?id='+project.ID+'&usr='+client.ID">Бриф <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                                    <li><a :href="'/kabinet/projects/planning/?p='+project.ID+'&usr='+client.ID">Планирование задач <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                                </ul>
                        </div>
                    </td>
                    <td>
                        <div v-if="typeof datatask[client.ID] == 'undefined'">У клиента еще нет задач</div>

                        <div v-for="project in dataproject[client.ID]">
                            <div v-for="task in datatask[client.ID]">
                                <table v-if="project.ID == task.UF_PROJECT_ID">
                                    <tr>
                                        <td>
                                            {{badTask(task.ID,client.ID,project.UF_ORDER_ID,task.UF_PRODUKT_ID)}}
                                            <div class="d-flex" v-if="typeof dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID] !='undefined'">
                                                <div><img :src="dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID].PREVIEW_PICTURE_SRC"></div>
                                                <div class="ml-3">
                                                    <div class="h4" style="margin-top: 0;"><a :href="'/kabinet/projects/reports/?t='+task.ID+'&usr='+client.ID">{{task.UF_NAME}}</a></div>
                                                    <div class="">Стоимость: <span class="text-danger" style="font-size: 23px;">{{task.FINALE_PRICE}} <span class="text-danger" v-if="task.UF_CYCLICALITY == 2">руб./месяц</span><span class="text-danger" v-if="task.UF_CYCLICALITY != 2">руб.</span></span></div>
                                                    <div class="info-blk">Количество: <span>{{task.UF_NUMBER_STARTS}}</span></div>
                                                    <div class="info-blk">Дата создания: <span>{{task.UF_PUBLISH_DATE_ORIGINAL.FORMAT1}}</span></div>
                                                    <div class="info-blk">Дата завершения: <span>{{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</span></div>
                                                    <div class="info-blk">Согласование: <span>{{viewListFieldTitle(task,'UF_COORDINATION')}}</span></div>
                                                    <div class="info-blk">Отчетность: <span>{{viewListFieldTitle(task,'UF_REPORTING')}}</span></div>
                                                    <div class="info-blk">Тип процесса: <span>{{viewListFieldTitle(task,'UF_CYCLICALITY')}}</span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <form action="/kabinet/admin/performances/" method="post">
                                                <input type="hidden" name="clientidsearch" :value="client.ID">
                                                <div class="form-group select-status" v-for="(TitleStatus,idStatus) in statusCatalog()">
                                                    <div class="form-check" v-if="getExecutionStatusCount2(task.ID,idStatus)>0">
                                                        <input @change="gotocearchstatus" name="statusexecutionsearch" class="form-check-input" :id="$id(idStatus)" type="radio" :value="idStatus">
                                                        <label class="form-check-label text-primary" :for="$id(idStatus)">{{TitleStatus}} - <span class="badge badge-secondary">{{getExecutionStatusCount(client.ID,idStatus)}}</span></label>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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