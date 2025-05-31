<?php
namespace Bitrix\telegram\handlers;

use \Bitrix\telegram\contracts\Telegramcommandinterface;

class Reportscommand implements Telegramcommandinterface
{
    private $bot;

    public function __construct(\Bitrix\telegram\Telegrambothandler $bot)
    {
        $this->bot = $bot;
    }

    public function execute(array $message): void
    {
        $telegramId = $message['from']['id'];
        $authLink = $this->bot->generateAuthLink($telegramId);

        $task = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');
        $taskList = $task->getData();
        $all_id_task_list = array_column($taskList, 'ID');
        $Runner = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');
        $Fulfi = $Runner->getData($all_id_task_list);

        if($Fulfi) {
            $response = "Исполнения:\n";
            foreach ($Fulfi as $item){
                $taskData = [];
                $key = array_search($item['UF_TASK_ID'], array_column($taskList, 'ID'));
                if ($key !== false) $taskData = $taskList[$key];

                $response .= "#{$item['UF_EXT_KEY']} исполнение для задачи {$taskData['UF_NAME']} {$item['UF_STATUS_ORIGINAL']['TITLE']} с {$item['UF_CREATE_DATE_ORIGINAL']['FORMAT1']}\n";
            }
        }else{
            $response = "У Вас пока нет запланированных задач:\n";
        }

        //$response .= "Согласование и отчеты https://kupi-otziv.ru/kabinet/reports/{$authLink}";

        $this->bot->sendMessage($message['chat']['id'], $response);
    }
}