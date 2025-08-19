<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

/*
 серверный обработчик, который сохраняет текущую дату в пользовательское поле UF_DOGOVOR_DATE
Проверки безопасности:

Убеждается, что запрос выполнен в контексте Bitrix

Проверяет авторизацию пользователя

Валидирует сессию через check_bitrix_sessid()

Логика работы:

Получает ID пользователя из запроса

Проверяет существование поля UF_DOGOVOR_DATE у пользователя

Если поле уже содержит дату - возвращает успешный статус без изменений

Если поле пустое - сохраняет текущую дату в формате "Короткая дата" Bitrix

Результат:

Возвращает JSON-ответ с статусом операции (success: true/false)

В случае ошибки содержит сообщение (message)

*/


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Проверяем авторизацию
global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    die();
}


if (!check_bitrix_sessid()) {
    die(json_encode(['success' => false, 'message' => 'Неверная сессия']));
}

// Получаем ID текущего пользователя
$userId = $_REQUEST['userId'];

// Проверяем, есть ли у пользователя поле UF_DOGOVOR_DATE
$user = new CUser;
$userFields = $user->GetByID($userId)->Fetch();
if (!array_key_exists('UF_DOGOVOR_DATE', $userFields)) {
    echo json_encode(['success' => false, 'message' => 'Поле UF_DOGOVOR_DATE не существует']);
    die();
}

// Если поле уже заполнено - ничего не делаем
if (!empty($userFields['UF_DOGOVOR_DATE'])) {
    echo json_encode(['success' => true, 'message' => 'Дата уже сохранена ранее']);
    die();
}

// Сохраняем текущую дату
$currentDate = ConvertTimeStamp(time(), 'SHORT');
$result = $user->Update($userId, ['UF_DOGOVOR_DATE' => $currentDate]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $user->LAST_ERROR]);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>