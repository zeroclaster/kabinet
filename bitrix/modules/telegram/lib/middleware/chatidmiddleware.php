<?php
namespace Bitrix\telegram\middleware;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\telegram\exceptions\TelegramMiddlewareException;
use Bitrix\telegram\contracts\Middlewareinterface;
use Bitrix\telegram\Telegrambothandler;

class Chatidmiddleware implements Middlewareinterface
{
    private Telegrambothandler $bot;

    public function __construct(Telegrambothandler $bot)
    {
        $this->bot = $bot;
    }

    public function handle(array $message, \Closure $next): void
    {
        // Проверяем, что это сообщение от пользователя (не канал/группа)
        if (!isset($message['from']['id']) || !isset($message['chat']['id'])) {
            throw new \Bitrix\telegram\exceptions\TelegramException('Invalid message format for chat ID processing');
        }

        $telegramUserId = $message['from']['id'];
        $chatId = $message['chat']['id'];


            Loader::includeModule('main');

            // Ищем пользователя по telegram_id
            $user = UserTable::getList([
                'filter' => ['=UF_TELEGRAM_ID' => $telegramUserId],
                'select' => ['ID', 'UF_TELEGRAM_CHAT_ID']
            ])->fetch();

            if ($user && empty($user['UF_TELEGRAM_CHAT_ID'])) {
                // Обновляем chat_id если его нет
                $userObj = new \CUser;
                $result = $userObj->Update($user['ID'], [
                    'UF_TELEGRAM_CHAT_ID' => $chatId
                ]);

                if (!$result) {
                    throw new \Bitrix\telegram\exceptions\TelegramException(
                        'Failed to save chat ID: ' . implode(', ', $result->getErrorMessages())
                    );
                }

                // Логируем действие
                $this->bot->log("Updated chat ID for user {$user['ID']}: {$chatId}");
            }

        try {
        } catch (\Exception $e) {
            $this->bot->log("Chatidmiddleware error: " . $e->getMessage());
            throw new \Bitrix\telegram\exceptions\TelegramException('Chat ID processing failed');
        }

        // Передаем сообщение дальше по цепочке middleware
        $next($message);
    }
}