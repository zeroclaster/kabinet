<?php
namespace Bitrix\telegram;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Commandfactory
{
    public static function create(string $command, Telegrambothandler $bot): ? Telegramcommandinterface
    {
        return match($command) {
            '/www' => new \Bitrix\telegram\handlers\Maincommand($bot),
            '/kabinet' => new \Bitrix\telegram\handlers\Kabinetcommand($bot),
            '/profile' => new \Bitrix\telegram\handlers\Profilecommand($bot),
            '/finance' => new \Bitrix\telegram\handlers\Financecommand($bot),
            '/deposit' => new \Bitrix\telegram\handlers\Depositcommand($bot),
            '/reports' => new \Bitrix\telegram\handlers\Reportscommand($bot),
            default => null,
        };
    }
}
