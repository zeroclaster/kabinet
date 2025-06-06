<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Kabinetcommand implements Telegramcommandinterface
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

        $projects = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');
        $data = $projects->getData();

        if ($data) {
            $response = "Проекты:\n";
            foreach ($data as $project) {
                $response .= "{$project['UF_NAME']} #{$project['UF_EXT_KEY']}\n";
            }
        }
        else{
            $response = "У Вас пока нет проектов.\n";
        }

        $response .= "Посмотреть все проекты по ссылке <a href='https://kupi-otziv.ru/kabinet/{$authLink}'>https://kupi-otziv.ru/kabinet/</a>";
        $this->bot->sendMessage($message['chat']['id'], $response);
    }
}