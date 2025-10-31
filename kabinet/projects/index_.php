<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');
$projectData = $projectManager->getData();

$order = $projectManager->orderData();

$project = \PHelp::getElementByField($projectData,$request->get('id'));
if (!$project) LocalRedirect("/404.php", "404 Not Found");

?>

<div class="d-flex justify-content-between">
    <?$APPLICATION->IncludeComponent("bitrix:breadcrumb","",Array(
            "START_FROM" => "0",
            "PATH" => "",
            "SITE_ID" => "s1"
        )
    );?>
    <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="PLANNING" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>

</div>


<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'PLANNING',
    )
);?>


<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                    <div class="row">
                        <div id="projectinfocontent" class="col-md-12"></div>
                        <script type="text/html" id="project-info">
                                <h4>Проект</h4>
                                <div class="d-flex">
                                <h1 class="mr-3" v-if="!showEditTitle">{{project.UF_NAME}}</h1>
                                <div class="mr-3" v-if="showEditTitle"><input type="text" class="form-control" v-model="project.UF_NAME" @input="saveinput"></div>
                                <button class="btn btn-link" style="font-size: 25px;padding: 0;" @click="()=>showEditTitle=true" v-if="!showEditTitle"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                <button class="btn btn-link" style="font-size: 25px;padding: 0;" @click="()=>showEditTitle=false" v-if="showEditTitle"><i class="fa fa-times" aria-hidden="true"></i></button>
                                </div>
                                <div class="status-project" v-html="projectStatus()"></div>
                                <div>Ожидает оплаты Запустится автоматически после пополнения баланса.</div>
                        </script>

                   </div>

                <?/*
                *
                *       К А Л Е Н Д А Р Ь
                */?>
                <h4>Календарь задач проекта</h4>
                <div class="panel">
                    <div class="panel-header">
                    </div>
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

                <?$APPLICATION->IncludeComponent("exi:project.list", "detailed", Array(
                        'PROJECT_ID' => $project["ID"],
                    )
                );?>

            </div>
        </div>
    </div>
</section>


<?
$GLOBALS['message_filter'] = ['UF_PROJECT_ID'=>$project["ID"]];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "project_chat", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 5,                           // количество сообщений в чате
    )
);?>

<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','queueStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/calendar.task.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/applications/project.info.js");
?>
<script>
window.addEventListener("components:ready", function(event) {
    calendarTask_Application.start(<?=CUtil::PhpToJSObject(['PROJECT_ID'=>$project["ID"]], false, true)?>);
    project_info.start(<?=CUtil::PhpToJSObject(['PROJECT_ID'=>$project["ID"],], false, true)?>);
});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>