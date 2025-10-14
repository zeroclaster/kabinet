<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('telegram');
// Использование класса
if ($_GET['hash']) {
    // Параметр определяет, создавать ли пользователей автоматически
    $autoCreateUsers = false; // Можно вынести в настройки или передавать через параметр
    $handler = new \Bitrix\telegram\Authhandler($_GET, $autoCreateUsers);
    $handler->authenticate();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");