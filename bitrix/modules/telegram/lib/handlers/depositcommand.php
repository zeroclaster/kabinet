<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Depositcommand implements Telegramcommandinterface
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

        $this->bot->sendMessage($message['chat']['id'], "Пополнить баланс https://kupi-otziv.ru/kabinet/finance/deposit/{$authLink}");
    }
}