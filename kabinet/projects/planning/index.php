<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Планирование");


$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$p = $request->get('p');
if ($p == null) LocalRedirect("/404.php");
$project = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getById($p)->fetch();
if (!$project) LocalRedirect("/404.php");

$APPLICATION->AddChainItem("Проект", "/kabinet/projects/?id=".$p);
$APPLICATION->AddChainItem("Планирование задач", "");
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

    <section class="">
        <div class="container-fluid">
            <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                    'CODE' => 'PLANNING',
                )
            );?>
        </div>
    </section>

<section class="section-xs">
    <div class="container-fluid">
       <h4>Проект: <?=$project['UF_NAME']?></h4>
        <div class="h1"><i class="fa fa-calendar" aria-hidden="true"></i> Планирование задач</div>
    </div>
</section>


    <section class="">
        <div class="container-fluid">
            <div class="row row-30">
                <div class="col-md-12">

				<h4>Календарь задач проекта</h4>
				<div class="panel">
                <div class="panel-body">
					<div class="row justify-content-md-center">	
								<div class="col-sm-8">
                                    <?
                                    //   bitrix/templates/kabinet/components/exi/task.list/.default/queue.data.php
                                    ?>

									<div id="calendar1" class="fullcalendar"></div>


                                    <?/*
                                    СТАТИСТИКА
                                    bitrix/templates/kabinet/components/exi/task.list/.default/queue.data.php
                                    запускается bitrix/templates/kabinet/components/exi/task.list/.default/task_list.js
                                    mounted()
                                    this.updatecalendare([],this.project_id);
                                    */?>
                                      <div class="d-flex">
                                          <div class="d-flex mr-5 align-items-center"><div id="done_calendar_counter" class="fc-event-light mr-2 p-2"></div> Выполнено</div>
                                          <div class="d-flex mr-5 align-items-center"><div id="inprogress_calendar_counter" class="fc-event-success mr-2 p-2"></div> Выполняются</div>
                                          <div class="d-flex mr-5 align-items-center"><div id="planned_calendar_counter" class="fc-event-warning mr-2 p-2"></div> Запланированы</div>
                                      </div>
								</div>	  
					</div>				  
                </div>
                </div>

                    <?$APPLICATION->IncludeComponent("exi:task.list", "", Array(
                            'PROJECT' => $project['ID']
                        )
                    );?>

                </div>
            </div>
        </div>
    </section>

	  
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>