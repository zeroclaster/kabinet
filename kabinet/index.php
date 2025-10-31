<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кабинет «Купи-Отзыв»");
?>

<?
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = $sL->get('Kabinet.Project');
$projects = $projectManager->getData();
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12" style="margin-bottom:0px;">
                <h1>Добро пожаловать, <?=$user->printName()?> <span class="grey-blk-1">ID<?=$user['ID']?></span></h1>

                <div class="d-flex justify-content-end"><div class="pagehelp-button text-primary" data-component="pagehelp" data-code="DASHBOARD"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div></div>
            </div>
        </div>
    </div>
</section>


<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'DASHBOARD',
    )
);?>

<?
/*
$APPLICATION->IncludeComponent("exi:billing.view", "dashboard", Array(
        'COUNT' => 2,                           // количество
        "FILTER_NAME"=>'',
    )
);
*/
?>

<?
// если нужно показывать только прочитанные
$GLOBALS['message_filter'] = [];
//$GLOBALS['message_filter'] = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
//$GLOBALS['message_filter'] = ['UF_TYPE'=>\Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 10,                           // количество сообщений в чате
        'NEW_RESET' => 'N',                   // фиксироваь пометку причитанные сообщения, N - не фиксировать
        "MODE" => 2
    )
);?>

<div class="pl-3"><a href="/kabinet/notifications/" class="h3 text-primary" style="margin-top: 0px;">Написать сообщение</a></div>

<section class="section-xs">
        <div class="container-fluid">
          <div class="row row-30">
				<div class="col-md-12">
                    <?$APPLICATION->IncludeComponent("exi:project.list", "", Array(
                    )
                    );?>
				</div>
			</div>
		</div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>