<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('telegram');
// Использование класса
if ($_GET['hash']) {
    $handler = new \Bitrix\telegram\Authhandler($_GET);
    $handler->authenticate();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");