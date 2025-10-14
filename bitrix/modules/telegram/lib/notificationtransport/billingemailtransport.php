<?php
namespace Bitrix\telegram\notificationtransport;

use Bitrix\Main\SystemException;
use Bitrix\telegram\contracts\Notificationtransportinterface;
use Bitrix\telegram\Exceptions\NotificationException;

class Billingemailtransport implements Notificationtransportinterface {
    public function send($recipientData, array $billingData, $message): bool {
        try {
            // Для биллинга получатель - это сам пользователь
            $userEmail = $recipientData['EMAIL'] ?? $recipientData->get('EMAIL');

            if (!$userEmail)  throw new NotificationException("Ошибка при отправки уведомлений о пополнений балланса");

            $result = \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME' => 'BILLING_NOTIFICATION',
                'LID' => SITE_ID,
                'C_FIELDS' => [
                    'EMAIL' => $userEmail,
                    'MESSAGE' => $message,
                    'OPERATION' => $billingData['UF_OPERATION'],
                    'VALUE' => $billingData['UF_VALUE'],
                ],
                "DUPLICATE"=> "N"
            ]);

            if (!$result->isSuccess()) {
                throw new NotificationException(implode(', ', $result->getErrorMessages()));
            }

            $this->log("Billing notification sent to {$userEmail}");
            return true;

        } catch (\Exception $e) {
            $this->logError("Billing email send failed: " . $e->getMessage());
            return false;
        }
    }

    private function log(string $message): void
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/billing_notifications.log',
            date('[Y-m-d H:i:s] ').$message."\n",
            FILE_APPEND
        );
    }

    private function logError(string $message): void
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/billing_errors.log',
            date('[Y-m-d H:i:s] ').$message."\n",
            FILE_APPEND
        );
    }
}