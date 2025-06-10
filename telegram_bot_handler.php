<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('kabinet');

// Получаем входящие данные от Telegram
//$input = file_get_contents("php://input");
//$data = json_decode($input, true);
//AddMessage2Log(print_r($data,true), "my_module_id");


$bot = new \Bitrix\telegram\Telegrambothandler();

// https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo
// URL вашего Bitrix24 вебхука
$bitrixWebhookUrl = 'https://im-ru.bitrix.info/imwebhook/eh/eb5f7f3bb1f743224ddd5112bd08c5411690206592/';

// Получаем данные из входящего запроса от Telegram
$input = file_get_contents('php://input');
$headers = getallheaders();

// Инициализируем cURL-сессию
$ch = curl_init($bitrixWebhookUrl);

// Устанавливаем опции для cURL
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Отправляем запрос к Bitrix24
$response = curl_exec($ch);

// Проверяем на ошибки
if (curl_errno($ch)) {
    http_response_code(500);
    $bot->log('Proxy error: ' . curl_error($ch));
} else {
    // Возвращаем тот же статус код и ответ, что и Bitrix24
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    http_response_code($statusCode);

    // Разделяем заголовки и тело ответа
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $bot->log($body);
}

// Закрываем cURL-сессию
curl_close($ch);


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