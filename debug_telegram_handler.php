<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('kabinet');


// Имитация Telegram Webhook запроса
$testMessage = [
    'update_id' => 168600263,
    'message' => [
        'message_id' => 25279,
        'from' => [
            'id' => 847797378,       // Ваш Telegram ID
            'is_bot' => false,
            'first_name' => 'Sergey',
            'last_name' => 'Suharkov',
            'username' => '',
            'language_code' => 'ru'
        ],
        'chat' => [
            'id' => 847797378,        // Chat ID (обычно = user ID)
            'first_name' => 'Sergey',
            'last_name' => 'Suharkov',
            'username' => '',
            'type' => 'private'
        ],
        'date' => time(),
        //'text' => '/reports',          // Тестируемая команда
       // 'text' => '/deposit',          // Тестируемая команда
        //'text' => '/finance',          // Тестируемая команда
        'text' => '/kabinet',          // Тестируемая команда
        //'text' => '/www',          // Тестируемая команда
        'entities' => [['offset' => ['length' => 8,'type' => 'bot_command']]]
        ]
];

// Преобразуем в JSON (как реальный запрос от Telegram)
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$testData = json_encode($testMessage);
file_put_contents('/var/www/kupi_otziv_r_usr/data/www/kupi-otziv.ru/test_webhook.json', $testData);



$bot = new \Bitrix\telegram\Testtelegrambothandler();
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