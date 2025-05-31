<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Financecommand implements Telegramcommandinterface
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

        $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
        $data = $billing->getData();
        $ACTUAL_MONTH_EXPENSES = $billing->actualMonthExpenses();
        $ACTUAL_MONTH_BUDGET = $billing->actualMonthBudget();
        $EXPENSES_NEXT_MONTH = $billing->nextMonthExpenses();
        [$nextMouthStart,$nextMouthEnd]  = \PHelp::nextMonth();

        $response =  "Ваш балланс {$data['UF_VALUE']} руб.\n";
        $response .=  "Расход в текущем месяце  {$ACTUAL_MONTH_EXPENSES} руб.\n";
        $response .=  "Бюджет на текущий месяц  {$ACTUAL_MONTH_BUDGET} руб.\n";
        $response .=  "Бюджет на следующий месяц с с {$nextMouthStart->format("d.m.Y")} по {$nextMouthEnd->format("d.m.Y")} {$EXPENSES_NEXT_MONTH} руб.\n";

        $response .= "Подробная информация о ваших финансах https://kupi-otziv.ru/kabinet/finance/{$authLink}";

        $this->bot->sendMessage($message['chat']['id'], $response);
    }
}