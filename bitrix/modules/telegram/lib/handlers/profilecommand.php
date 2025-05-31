<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Profilecommand implements Telegramcommandinterface
{
    private $bot;

    public function __construct(\Bitrix\telegram\Telegrambothandler $bot)
    {
        $this->bot = $bot;
    }

    public function execute(array $message): void
    {
        $user = $this->bot->getUserByTelegramId($message['from']['id']);
        $response = $user
            ? "Ваш профиль:\nИмя: {$user['NAME']}\nEmail: {$user['EMAIL']}"
            : "Вы не авторизованы на сайте.";

        $this->bot->sendMessage($message['chat']['id'], $response);
    }
}