<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('kabinet');

// Получаем входящие данные от Telegram
//$input = file_get_contents("php://input");
//$data = json_decode($input, true);
//AddMessage2Log(print_r($data,true), "my_module_id");

if(1) {
    $bot = new \Bitrix\telegram\Telegrambothandler();

//https://api.telegram.org/bot[BOT_API_TOKEN]/setWebhook?url=[YOUR_DOMAIN]/telegram_webhook.php
// https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo
// URL вашего Bitrix24 вебхука


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
}

if(1) {

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
       // $bot->log('Proxy error: ' . curl_error($ch));
        http_response_code(200);
        echo 'OK';
    } else {
        // Возвращаем тот же статус код и ответ, что и Bitrix24
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        http_response_code($statusCode);

        // Разделяем заголовки и тело ответа
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        http_response_code(200);
       // echo $body;
        echo 'OK';
    }

// Закрываем cURL-сессию
    curl_close($ch);
}
//$input = file_get_contents('php://input');
//file_put_contents('telegram_proxy.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);
//file_put_contents('telegram_proxy.log', date('Y-m-d H:i:s') . " - Response: " . $response . "\n", FILE_APPEND);

//http_response_code(200);
//echo 'OK';
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");