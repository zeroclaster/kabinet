<?
use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Планирование");


//$APPLICATION->AddChainItem("екнекгне", "/kabinet/projects/?id=28");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$user = (\KContainer::getInstance())->get('user');
$user_id = $user->get('ID');

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$ClientManager = $sL->get('Kabinet.Client');
$projectManager = $sL->get('Kabinet.Project');
$taskManager = $sL->get('Kabinet.Task');
$runnerManager = $sL->get('Kabinet.Runner');

$task_id = $request->get('t');

$QueueStatistics = $taskManager->getQueueStatistics($task_id);

$taskdata = $taskManager->getData();
$key = array_search($task_id, array_column($taskdata, 'ID'));
if ($key !== false){
    $taskdata = $taskdata[$key];
}
else{
   // throw new \Bitrix\Main\SystemException("Task data not found". "(".$task['ID'].")");
    ShowError("Task data not found". "(".$taskdata['ID'].")");
}

$runner = $runnerManager->getData($taskdata['ID']);

$project_data = $projectManager->getData();
$key = array_search($taskdata['UF_PROJECT_ID'], array_column($project_data, 'ID'));
if ($key === false) ShowError("Project data not found". "(".$taskdata['UF_PROJECT_ID'].")");
$project = $project_data[$key];

$user_order = $projectManager->orderData();
$user_order = $user_order[$project['UF_ORDER_ID']][$taskdata['UF_PRODUKT_ID']];

//\Dbg::print_r($taskdata);
?>
<section class="">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <div>
                <h4 style="margin: 0;">Проект</h4>
                <div><h1><?=$project['UF_NAME']?></h1></div>
                <h4>Задача</h4>
            </div>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="REPORTS" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>

<section class="">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'REPORTS',
            )
        );?>
    </div>
</section>


<section class="task-info-block">
    <div class="container-fluid">
        <div class="row row-30">
            <div id="taskinfocontent" class="col-md-12" data-taskinfo="">
            </div>
        </div>
    </div>
</section>

<script type="text/html" id="task-info-template">
    <div id="task<?=$taskdata['ID']?>" class="panel">
        <div class="panel-body">

            <div class="row">
                <div class="col-md-1">
                    <img src="<?=$user_order['PREVIEW_PICTURE_SRC']?>" alt="<?=$taskdata['UF_NAME']?>">
                </div>
                <div class="col-md-8">
                    <div class="h3" style="margin-top:0px;"><?=$taskdata['UF_NAME']?></div>

                    <div class="d-flex task-status-print h4" v-html="taskStatus_m(TASK_ID)"></div>

                    <div class="mt-3">
                        <div class="d-flex">
                            <div class="d-flex mr-3 align-items-center">Запланированы - <div class="fc-event-light ml-2 mr-2"><?=$QueueStatistics[0]['COUNT']?></div></div>
                            <div class="d-flex mr-3 align-items-center">Выполняются - <div class="fc-event-success ml-2 mr-2"><?=$QueueStatistics[1]['COUNT']?></div></div>
                            <div class="d-flex mr-3 align-items-center">Выполнено - <div class="fc-event-warning ml-2 mr-2"><?=$QueueStatistics[2]['COUNT']?></div></div>
                        </div>
                        <div>Примерная частота выполнения: 1 ед. <?=\PHelp::dimensiontimeConvert($user_order['MINIMUM_INTERVAL']['VALUE'])?></div>
                        <div>Завершится: <?=$taskdata['UF_DATE_COMPLETION_ORIGINAL']['FORMAT1']?></div>
                        <?if($taskdata['UF_TARGET_SITE_ORIGINAL']):?>

                                <div class="d-flex link-block">
                                    <div class="mr-4">Ссылка:</div>
                                    <div class="link-block-value"><?
                                        foreach ($taskdata['UF_TARGET_SITE_ORIGINAL'] as $linksite) if ($linksite['VALUE']){
                                            $link_ = $linksite['VALUE'];
                                            echo "<div><a href='{$link_}' target='_blank' rel=\"nofollow\">{$link_}</a></div>";
                                        }
                                        ?></div>
                                </div>

                        <?endif;?>
                    </div>

                    <div class="mt-3">
                        <?
                        $key = array_search($taskdata['UF_CYCLICALITY'], array_column($taskdata['UF_CYCLICALITY_ORIGINAL'], 'ID'));
                        if ($key !== false){
                            ?>
                            <?=$taskdata['UF_CYCLICALITY_ORIGINAL'][$key]['VALUE']?>
                        <?}?>
                    </div>


                </div>
                <div class="col-md-3">
                    <ul class="list-unstyled">
                        <li><a href="/kabinet/projects/planning/?p=<?=$project['ID']?>#produkt<?=$taskdata['UF_PRODUKT_ID']?>">Планирование</a></li>
                        <li><a href="/kabinet/projects/breif/?id=<?=$project['ID']?>">Редактировать бриф</a></li>
                    </ul>
                </div>
            </div> <!-- <div class="row"> -->

        </div> <!-- <div class="panel-body"> -->
    </div>
</script>

<?/*
                *
                *       К А Л Е Н Д А Р Ь
                */?>
<?/*
<section class="section-xs">
    <div class="container-fluid">
    <div class="row row-30">
    <div class="col-md-12">

        <h4>Календарь задачи</h4>
    <div class="panel">
        <div class="panel-body">
            <div class="row justify-content-md-center">
                <div class="col-sm-8">
                    <div id="calendar1" class="fullcalendar"></div>
                    <div class="d-flex" id="calendar1vue">
                        <div class="d-flex mr-5 align-items-center"><div id="done_calendar_counter" class="fc-event-light mr-2 p-2">
                                0</div> Выполнено</div>
                        <div class="d-flex mr-5 align-items-center"><div id="inprogress_calendar_counter" class="fc-event-success mr-2 p-2">
                                0</div> Выполняются</div>
                        <div class="d-flex mr-5 align-items-center"><div id="planned_calendar_counter" class="fc-event-warning mr-2 p-2">
                                0</div> Запланированы</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>
    </div>
</section>
*/?>


<?if(!$runner):?>
    <div class="alert alert-danger" role="alert">
        Нет запланированных задач
    </div>
<?endif;?>

<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">

                <h2>Согласование и отчеты</h2>
                <div>Проверьте и, если необходимо, отредактируйте текст и нажмите "на публикацию". Вы можете отклонить текст, указав в комментарии, как нам его переписать.</div>

                <div class="panel filter-block">
                    <div class="panel-body">
                        <?$APPLICATION->IncludeComponent("exi:client.filterreport", "", Array(
                                'FILTER_NAME' => 'clientfilter1',
                            )
                        );?>
                        <?
						// for debugg!!!
                        global $clientfilter1;
                        //\Dbg::var_dump($clientfilter1);
                        ?>
                    </div>
                </div>

                <?
                // filter debugg !
                //print_r($GLOBALS['clientfilter1']);
                $query_queue = $request->get('queue');
                if ($query_queue){
                    $GLOBALS['clientfilter1']['queue_id'] = $query_queue;
                }
                ?>

                <?$APPLICATION->IncludeComponent("exi:reports.list", "", Array(
                        'TASK_ID' => $task_id,
                        'COUNT' => $_REQUEST['viewcount'],
                        "FILTER_NAME" => "clientfilter1",
                        'MESSAGE_COUNT' => 5,
                        'REDIRECT_404'=> 'Y',
                        'SHOW_404' =>'Y',
                    )
                );?>
            </div>
        </div>
    </div>
</section>


<?
(\KContainer::getInstance())->get('queueStore');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/calendar.reports.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/reports.list/.default/js/task.info.js");
?>
<script>
    components.tasklist22 = {
        selector: '[data-taskinfo]',
        script: [
            '../../kabinet/components/exi/task.list/.default/task_status.js'
        ],
        init:null
    }

    window.addEventListener("components:ready", function(event) {

        task_info.start(<?=CUtil::PhpToJSObject([
                'TASK_ID' => $task_id
        ], false, true)?>);

	   /*
	   calendar_reports.start(<?=CUtil::PhpToJSObject([
          "TASK_ID"=>$taskdata['ID'],
        ], false, true)?>);
		*/
    });
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>