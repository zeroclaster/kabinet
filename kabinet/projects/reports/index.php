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


$runner = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner')->getTaskFulfiData($taskdata['ID']);

$project_data = $projectManager->getData();
$key = array_search($taskdata['UF_PROJECT_ID'], array_column($project_data, 'ID'));
if ($key === false) ShowError("Project data not found". "(".$taskdata['UF_PROJECT_ID'].")");
$project = $project_data[$key];

$user_order = $projectManager->orderData();
$user_order = $user_order[$project['UF_ORDER_ID']][$taskdata['UF_PRODUKT_ID']];

//\Dbg::print_r($taskdata);
?>
<section class="mt-4">
    <div class="container-fluid">
        <div class="d-flex no-d-flex justify-content-between">
            <div>
                <div>
                    <h4 style="margin: 0;margin-bottom: 4px;">Проект: <?=$project['UF_NAME']?> #<?=$project['UF_EXT_KEY']?></h4>
                </div>
                <div><h1>Ход работы</h1></div>
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
            <?$APPLICATION->IncludeComponent("exi:task.info.block", "", Array(
                )
            );?>
        </div>
    </div>
</section>


<?if(!$runner):?>
    <div class="alert alert-danger" role="alert">
        Нет запланированных задач
    </div>
<?endif;?>

<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">



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

                /*
                $query_queue = $request->get('queue');
                if ($query_queue){
                    $GLOBALS['clientfilter1']['queue_id'] = $query_queue;
                }
                */
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
?>
<script>
    window.addEventListener("components:ready", function(event) {
        /*
        calendar_reports.start(<?=CUtil::PhpToJSObject([
        "TASK_ID"=>$taskdata['ID'],
    ], false, true)?>);
    */
    });
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>