<?php
namespace Bitrix\telegram\notificationtransport;

use Bitrix\Main\SystemException;
use Bitrix\telegram\contracts\Notificationtransportinterface;
use Bitrix\telegram\Exceptions\NotificationException;

class Emailtransport implements Notificationtransportinterface {
    public function send($recipientData, array $messageData, $message): bool {
        try {
            if ($this->getRecipientSource($recipientData) == 'FulfillmentTable') $userParams = $recipientData->get('TASK')->get("USER");
            if ($this->getRecipientSource($recipientData) == 'TaskTable') $userParams = $recipientData->get("USER");
            if ($this->getRecipientSource($recipientData) == 'ProjectsTable') $userParams = $recipientData->get("USER");
            if ($this->getRecipientSource($recipientData) == 'UserTable') $userParams = $recipientData;

            $result = \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME' => 'KABINET_NOTIFICATION',
                'LID' => SITE_ID,
                'C_FIELDS' => [
                    'EMAIL' => $userParams['EMAIL'],
                    'MESSAGE' => $message,
                ],
                "DUPLICATE"=> "N"
            ]);

            if (!$result->isSuccess()) {
                throw new NotificationException(implode(', ', $result->getErrorMessages()));
            }
        } catch (\Exception $e) {
            $message = "Email send failed to {$recipientData['USER_DATA']['EMAIL']}: " . $e->getMessage();
            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'].'/telegram_bot.log',
                date('[Y-m-d H:i:s] ').$message."\n",
                FILE_APPEND
            );
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