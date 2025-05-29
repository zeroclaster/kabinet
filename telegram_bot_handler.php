<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

define("BOT_TOKEN", "6693650729:AAGpdYX9_q7IltFBNtjGLmZ7GNATMVYcM3I");

// Получаем входящие данные от Telegram
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Проверяем, что запрос пришел от Telegram
if (empty($data) || !isset($data['update_id'])) {
    die("Invalid request");
}

// Обрабатываем команды
if (isset($data['message']['text'])) {
    $chatId = $data['message']['chat']['id'];
    $text = $data['message']['text'];
    $userId = $data['message']['from']['id'];

    switch ($text) {
        case '/kabinet':
            $response = "Привет! Я бот для сайта. /kabinet";
            sendMessage($chatId, $response);
            break;

        case '/profile':
            // Получаем данные пользователя из Битрикс по telegram_id
            $user = getUserByTelegramId($userId);
            if ($user) {
                $response = "Ваш профиль:\nИмя: " . $user['NAME'] . "\nEmail: " . $user['EMAIL'];
            } else {
                $response = "Вы не авторизованы на сайте.";
            }
            sendMessage($chatId, $response);
            break;

        default:
            sendMessage($chatId, "Неизвестная команда. Напишите /help для списка команд.");
    }
}

// Функция отправки сообщения в Telegram
function sendMessage($chatId, $text) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $params = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Функция поиска пользователя в Битрикс по Telegram ID
function getUserByTelegramId($telegramId) {
    CModule::IncludeModule("main");

    $rsUser = CUser::GetList(
        ($by = "ID"),
        ($order = "ASC"),
        ['UF_TELEGRAM_ID' => $telegramId], // Пользовательское поле для хранения telegram_id
        ['SELECT' => ['ID', 'NAME', 'EMAIL']]
    );

    return $rsUser->Fetch();
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");