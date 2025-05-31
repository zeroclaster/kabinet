<?php

use Bitrix\Kabinet\exceptions\MessangerException;

\Bitrix\Main\Loader::registerAutoLoadClasses("telegram", array(
    "Bitrix\\telegram\Exceptions\TelegramException" => "lib/exceptions/exceptions.php",
    "Bitrix\\telegram\Exceptions\TelegramAuthException" => "lib/exceptions/exceptions.php",
    "Bitrix\\telegram\Exceptions\TelegramMiddlewareException" => "lib/exceptions/exceptions.php",
));


//throw new \Bitrix\Kabinet\exceptions\MessangerException(\Bitrix\Main\Entity\Query::getLastQuery());

AddEventHandler("", "\Lmessanger::OnAfterAdd", function ($id, $primary, $fields, $object) {
    // 1. Валидация входящих данных
    if (empty($fields['UF_MESSAGE_TEXT'])) {
        AddMessage2Log('Empty message text', 'telegram_error');
        return;
    }

    // 2. Инициализация сервисов
    $bot = new \Bitrix\telegram\Testtelegrambothandler();
    $messengerService = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');

    // 3. Определение типа сообщения
    $isSystem = $fields['UF_TYPE'] == \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE;
    $isForUser = $fields['UF_TYPE'] == \Bitrix\Kabinet\messanger\Messanger::USER_MESSAGE;

    // 4. Получение данных об авторе
    $messageData = $messengerService->getData(
        $filter = ['ID'=>$id],
        $offset=0,
        $limit=1,
        $clear=true,
        $new_reset='n'
    );

    $authorName = $messageData[0]['UF_AUTHOR_ID_ORIGINAL']['PRINT_NAME'] ?? '';
    $messageText = prepareMessage($fields['UF_MESSAGE_TEXT'], $isForUser, $authorName);

    // 5. Поиск получателя и формирование сообщения
    try {
        $recipientData = getRecipientData($fields);
        if (!$recipientData || empty($recipientData['UF_AUTHOR_ID'])) {
            throw new \Exception('Recipient not found');
        }

        $fullMessage = buildFullMessage(
            $messageText,
            $isSystem,
            $recipientData
        );

        sendNotification($bot, (int)$recipientData['UF_AUTHOR_ID'], $fullMessage);

    } catch (\Exception $e) {
        AddMessage2Log(
            'Message processing failed: ' . $e->getMessage(),
            'telegram_error'
        );
    }
});

/**
 * Получает данные получателя в зависимости от типа сообщения
 */
function getRecipientData(array $fields): ?array
{
    if ($fields['UF_QUEUE_ID'] > 0) {
        return \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getList([
            'select' => [
                'UF_AUTHOR_ID' => 'TASK.UF_AUTHOR_ID',
                'TASK_ID' => 'TASK.ID',
                'TASK_NAME' => 'TASK.UF_NAME',
                'TASK_EXT_KEY' => 'TASK.UF_EXT_KEY',
                'PROJECT_ID' => 'PROJECT.ID',
                'PROJECT_NAME' => 'PROJECT.UF_NAME',
                'PROJECT_EXT_KEY' => 'PROJECT.UF_EXT_KEY',
            ],
            'filter' => ['ID' => (int)$fields['UF_QUEUE_ID']],
            'runtime' => [
                'TASK' => [
                    'data_type' => \Bitrix\Kabinet\task\datamanager\TaskTable::class,
                    'reference' => ['=this.UF_TASK_ID' => 'ref.ID'],
                    'join_type' => 'INNER'
                ],
                'PROJECT' => [
                    'data_type' => \Bitrix\Kabinet\project\datamanager\ProjectsTable::class,
                    'reference' => ['=this.TASK.UF_PROJECT_ID' => 'ref.ID'],
                    'join_type' => 'INNER'
                ],
            ],
            'limit' => 1
        ])->fetch() ?: null;
    }

    if ($fields['UF_TASK_ID'] > 0) {
        return \Bitrix\Kabinet\task\datamanager\TaskTable::getList([
            'select' => [
                'UF_AUTHOR_ID',
                'TASK_ID' => 'ID',
                'TASK_NAME' => 'UF_NAME',
                'TASK_EXT_KEY' => 'UF_EXT_KEY',
                'PROJECT_ID' => 'PROJECT.ID',
                'PROJECT_NAME' => 'PROJECT.UF_NAME',
                'PROJECT_EXT_KEY' => 'PROJECT.UF_EXT_KEY',
            ],
            'filter' => ['ID' => (int)$fields['UF_TASK_ID']],
            'runtime' => [
                'PROJECT' => [
                    'data_type' => \Bitrix\Kabinet\project\datamanager\ProjectsTable::class,
                    'reference' => ['=this.UF_PROJECT_ID' => 'ref.ID'],
                    'join_type' => 'INNER'
                ],
            ],
            'limit' => 1
        ])->fetch() ?: null;
    }

    if ($fields['UF_PROJECT_ID'] > 0) {
        return \Bitrix\Kabinet\project\datamanager\ProjectsTable::getList([
            'select' => [
                'UF_AUTHOR_ID',
                'PROJECT_ID' => 'ID',
                'PROJECT_NAME' => 'UF_NAME',
                'PROJECT_EXT_KEY' => 'UF_EXT_KEY',
            ],
            'filter' => ['ID' => (int)$fields['UF_PROJECT_ID']],
            'limit' => 1
        ])->fetch() ?: null;
    }

    if ($fields['UF_TARGET_USER_ID'] > 0) {
        return ['UF_AUTHOR_ID' => (int)$fields['UF_TARGET_USER_ID']];
    }

    return null;
}

/**
 * Подготавливает основное сообщение
 */
function prepareMessage(string $text, bool $isForUser, string $authorName): string
{
    $cleaned = trim(strip_tags($text));
    $message = mb_substr($cleaned, 0, 4096);

    return $isForUser
        ? "<p>{$authorName}</p><p>{$message}</p>"
        : $message;
}

/**
 * Формирует полное сообщение с метаданными
 */
function buildFullMessage(string $message, bool $isSystem, array $recipientData): string
{
    if (!$isSystem) {
        return $message;
    }

    $parts = [];

    if (!empty($recipientData['PROJECT_NAME'])) {
        $parts[] = "Проект «{$recipientData['PROJECT_NAME']}» #{$recipientData['PROJECT_EXT_KEY']}";
    }

    if (!empty($recipientData['TASK_NAME'])) {
        $taskLink = "https://kupi-otziv.ru/kabinet/projects/reports/?t={$recipientData['TASK_ID']}";
        $parts[] = "Задача <a href=\"{$taskLink}\">«{$recipientData['TASK_NAME']}» #{$recipientData['TASK_EXT_KEY']}</a>";
    }

    return implode(', ', $parts) . $message;
}

/**
 * Отправляет уведомление в Telegram
 */
function sendNotification($bot, int $userId, string $message): void
{
    try {
        $bot->sendMessageToUserTelegram($userId, $message);
    } catch (\Bitrix\telegram\exceptions\TelegramException $e) {
        AddMessage2Log(
            "Telegram send failed to user {$userId}: " . $e->getMessage(),
            'telegram_error'
        );
        throw $e;
    }
}
