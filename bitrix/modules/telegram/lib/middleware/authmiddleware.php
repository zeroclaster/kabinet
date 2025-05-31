<?php
namespace Bitrix\telegram\middleware;

use Bitrix\telegram\exceptions\TelegramAuthException,
 \Bitrix\telegram\contracts\Middlewareinterface,
    \Bitrix\telegram\Telegrambothandler;

class Authmiddleware implements Middlewareinterface
{
    private Telegrambothandler $bot;

    public function __construct(Telegrambothandler $bot)
    {
        $this->bot = $bot;
    }

    public function handle(array $message, \Closure $next): void
    {
        $telegramId = $message['from']['id'];

        // Получаем пользователя Bitrix
        $user = $this->bot->getUserByTelegramId($telegramId);

        if (!$user) {
            throw new TelegramAuthException(
                "Пользователь не привязан к аккаунту. Используйте /start для авторизации.",
                $message['chat']['id']
            );
        }

        // Login parser user
        global $USER;
        if (!is_object($USER)) $USER = new \CUser;
        $USER->Authorize($user['ID']);

        onPrologBootstrape();

        // Добавляем user data в сообщение для команд
        $message['bitrix_user'] = $user;

        $next($message); // Передаем дальше по цепочке
    }
}