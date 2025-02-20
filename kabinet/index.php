<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кабинет");
?>


<?
$user = (\KContainer::getInstance())->get('user');
$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = $sL->get('Kabinet.Project');
$projects = $projectManager->getData();
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <h1>Добро пожаловать, <?=$user->printName()?></h1>

                <div class="d-flex justify-content-end"><div class="pagehelp-button text-primary" data-component="pagehelp" data-code="DASHBOARD"><i class="fa fa-question-circle text-warning" aria-hidden="true"></i> Помощь</div></div>
            </div>
        </div>
    </div>
</section>

<section class="section-xs">
    <div class="container-fluid">
<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'DASHBOARD',
    )
);?>
    </div>
</section>

<?$APPLICATION->IncludeComponent("exi:billing.view", "dashboard", Array(
        'COUNT' => 2,                           // количество
        "FILTER_NAME"=>'',
    )
);?>

<?
// если нужно показывать только прочитанные
//$GLOBALS['message_filter'] = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
$GLOBALS['message_filter'] = ['UF_TYPE'=>\Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 5,                           // количество сообщений в чате
        'NEW_RESET' => 'N',                   // фиксироваь пометку причитанные сообщения, N - не фиксировать
    )
);?>

<section class="section-xs">
        <div class="container-fluid">
          <div class="row row-30">

              <?/*
				<div class="col-md-12">
					<div class="panel">
                        <?if(count($projects)==0):?>
						<div class="panel-header">
						  <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
                              <h4 class="panel-title">Создайте первый проект</h4>
						  </div>
						</div>
                        <?endif;?>
						<div class="panel-body">
						<a class="btn btn-primary mdi-plus icon-button" href="/kabinet/projects/breif/">Создать новый проект</a>
						</div>
					</div>
				</div>
              */?>

				<div class="col-md-12">
                    <?$APPLICATION->IncludeComponent("exi:project.list", "", Array(
                    )
                    );?>
				</div>	
			</div>
		</div>	
</section>
	  
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>