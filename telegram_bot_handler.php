<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('kabinet');

// Получаем входящие данные от Telegram
//$input = file_get_contents("php://input");
//$data = json_decode($input, true);
//AddMessage2Log(print_r($data,true), "my_module_id");


$bot = new \Bitrix\telegram\Telegrambothandler();
$bot->addMiddleware(new \Bitrix\telegram\middleware\Loggingmiddleware());
$bot->addMiddleware(new \Bitrix\telegram\middleware\Authmiddleware($bot));
$bot->addMiddleware(new \Bitrix\telegram\middleware\Chatidmiddleware($bot));
//$bot->addMiddleware(new \Bitrix\telegram\middleware\Admincheckmiddleware());
try {
    $bot->handleRequest();
} catch (\Bitrix\telegram\exceptions\TelegramException $e) {
    // Обработка ошибки (логирование, уведомление и т.д.)
    AddMessage2Log($e->getMessage(), 'telegram');
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");