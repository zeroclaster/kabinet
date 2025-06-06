<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Maincommand implements Telegramcommandinterface
{
    private $bot;

    public function __construct(\Bitrix\telegram\Telegrambothandler $bot)
    {
        $this->bot = $bot;
    }

    public function execute(array $message): void
    {
        $telegramId = $message['from']['id'];
        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');

        $authLink = $this->bot->generateAuthLink($telegramId);

        $this->bot->sendMessage($message['chat']['id'], "Перейти на главную страницу https://kupi-otziv.ru/{$authLink}");
    }
}