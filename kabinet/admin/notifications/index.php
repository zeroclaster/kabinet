<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Все комментарии к исполнениям");
?>

<?php
if($_SERVER["REMOTE_ADDR"]!='176.212.216.251'){
    //exit;
}
?>

<?
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
?>

<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-bell" aria-hidden="true"></i> Все сообщения</h1>
            <div><button class="to-bottom btn btn-sm btn-primary"><span class="fa-chevron-down"></span></button></div>
        </div>
    </div>
</section>

<?
$adminUsers = \PHelp::usersGroup(MANAGER);
$id_list = array_column($adminUsers,"ID");

// если нужно показывать только прочитанные
//$GLOBALS['message_filter'] = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
$GLOBALS['message_filter'] = [];
$GLOBALS['message_filter'] = [
        'LOGIC' => 'AND',
        ['LOGIC' => 'OR','UF_AUTHOR_ID'=>$id_list,'UF_TARGET_USER_ID'=>$id_list]];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "admin-notification-page", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 100,                           // количество сообщений в чате
    )
);?>

<script>
    /*
BX.ready(function () {
    setTimeout(function () {
        const el = document.querySelector("footer");
        if (el) el.scrollIntoView({behavior: 'smooth'});
    },1000)

});
     */
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>