<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сообщения");
?>

<?
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
?>

<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-bell" aria-hidden="true"></i> Сообщения</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="NOTIFICATION" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>

<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'NOTIFICATION',
    )
);?>


<?
// если нужно показывать только прочитанные
//$GLOBALS['message_filter'] = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
$GLOBALS['message_filter'] = [];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "notification-page", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 100,                           // количество сообщений в чате
    )
);?>

<script>
BX.ready(function () {
    setTimeout(function () {
        const el = document.querySelector("footer");
        if (el) el.scrollIntoView({behavior: 'smooth'});
    },1000)

});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>