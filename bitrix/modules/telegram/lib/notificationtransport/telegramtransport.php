<?php
namespace Bitrix\telegram\notificationtransport;

use Bitrix\telegram\contracts\Notificationtransportinterface;
use Bitrix\telegram\exceptions\TelegramException;

class Telegramtransport implements Notificationtransportinterface {
    public function send($recipientData, array $messageData, $message): bool {

        if ($this->getRecipientSource($recipientData) == 'FulfillmentTable') $userParams = $recipientData->get('TASK')->get("USER");
        if ($this->getRecipientSource($recipientData) == 'TaskTable') $userParams = $recipientData->get("USER");
        if ($this->getRecipientSource($recipientData) == 'ProjectsTable') $userParams = $recipientData->get("USER");
        if ($this->getRecipientSource($recipientData) == 'UserTable') $userParams = $recipientData;

        //$bot = new \Bitrix\telegram\Testtelegrambothandler();
        $bot = new \Bitrix\telegram\Telegrambothandler();
        try {
            $bot->sendMessageToUserTelegram($userParams['ID'], $message);
        } catch (TelegramException $e) {
            $bot->log("Telegram send failed to user {$userParams['ID']}: " . $e->getMessage());
        }

        return true;
    }

    private function getRecipientSource($recipientData): string
    {
        if ($recipientData instanceof \Bitrix\Kabinet\taskrunner\datamanager\Fulfillment) {
            return 'FulfillmentTable';
        } elseif ($recipientData instanceof \Bitrix\Kabinet\task\datamanager\Task) {
            return 'TaskTable';
        } elseif ($recipientData instanceof \Bitrix\Kabinet\project\datamanager\Project) {
            return 'ProjectsTable';
        } elseif ($recipientData instanceof \Bitrix\Main\User) {
            return 'UserTable';
        }
        return 'unknown';
    }
}