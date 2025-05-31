<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

define("BOT_TOKEN", "6693650729:AAGpdYX9_q7IltFBNtjGLmZ7GNATMVYcM3I");
CModule::IncludeModule('telegram');
if ($_GET['hash']) {
    $auth_data = $_GET;
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);

    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }

    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    $secret_key = hash('sha256', BOT_TOKEN, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);

    if (strcmp($hash, $check_hash) !== 0) {
        die('Данные не из Telegram');
    }

    if ((time() - $auth_data['auth_date']) > 86400) {
        die('Данные устарели');
    }

    // Используем id пользователя Telegram в качестве логина, если username не указан
    $login = !empty($auth_data['username']) ? $auth_data['username'] : 'tg_'.$auth_data['id'];

    // Поиск пользователя в Битрикс
    $user = CUser::GetByLogin($login)->Fetch();

    if (!$user) {
        // Генерируем случайный пароль
        $password = bin2hex(random_bytes(8)); // Более безопасный способ генерации пароля

        // Создание нового пользователя
        $user = new CUser;
        $arFields = array(
            "LOGIN" => $login,
            "NAME" => $auth_data['first_name'] ?? '',
            "LAST_NAME" => $auth_data['last_name'] ?? '',
            "EMAIL" => $login."@telegram.user",
            "PASSWORD" => $password,
            "CONFIRM_PASSWORD" => $password,
            "ACTIVE" => "Y",
            "GROUP_ID" => array(2) // Группа "авторизованные пользователи"
        );

        $ID = $user->Add($arFields);
        if ($ID <= 0) {
            die($user->LAST_ERROR);
        }

        // Добавляем фото пользователя, если есть photo_url
        if (!empty($auth_data['photo_url'])) {
            $photoPath = downloadTelegramPhoto($auth_data['photo_url'], $ID);
            if ($photoPath) {
                $userObj = new CUser;
                $userObj->Update($ID, array('PERSONAL_PHOTO' => CFile::MakeFileArray($photoPath)));
                unlink($photoPath); // Удаляем временный файл
            }
        }

        // Получаем данные только что созданного пользователя
        $user = CUser::GetByID($ID)->Fetch();

        $botToken = "6693650729:AAGpdYX9_q7IltFBNtjGLmZ7GNATMVYcM3I";
        $bot = new \Bitrix\telegram\Telegrambothandler($botToken);
        $bot->sendMessage(
            $auth_data['id'],
            "Нажмите /start в чате с ботом, чтобы активировать уведомления"
        );
    }

    // После авторизации сохраняем telegram_id в профиль пользователя
    if ($user['ID']) {
        $userObj = new CUser;
        $userObj->Update($user['ID'], ['UF_TELEGRAM_ID' => $auth_data['id']]);
    }

    // Авторизация пользователя
    $USER->Authorize($user['ID']);



    LocalRedirect('/kabinet');
}

/**
 * Скачивает фото пользователя из Telegram
 *
 * @param string $photoUrl URL фотографии
 * @param int $userId ID пользователя
 * @return string|false Путь к временному файлу или false при ошибке
 */
function downloadTelegramPhoto($photoUrl, $userId) {
    $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/telegram_photos/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $tempFile = $tempDir . 'photo_' . $userId . '_' . time() . '.jpg';

    $ch = curl_init($photoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $photoData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($photoData)) {
        file_put_contents($tempFile, $photoData);
        return $tempFile;
    }

    return false;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");