<?php
namespace Bitrix\telegram;

use Bitrix\Kabinet\billing\datamanager\BillinghistoryTable;
use Bitrix\telegram\contracts\Notificationtransportinterface;
use Bitrix\telegram\Exceptions\NotificationException;

class Billingnotificationhandler
{
    public function __construct()
    {
    }

    public function handleBillingOperation(int $billingId) {
        try {
            $billingData = BillinghistoryTable::getById($billingId)->fetch();

            if (!$billingData || !$billingData['UF_ACTIVE']) {
                return;
            }

            if((float)$billingData['UF_VALUE'] == 0) return;

            if (strpos($billingData['UF_OPERATION'], 'Пополнение') === false &&
                stripos($billingData['UF_OPERATION'], 'Списание') === false
                //|| stripos($billingData['UF_OPERATION'], 'Комиссионный') === false
            ) {
                return;
            }

            if ($billingData['UF_AUTHOR_ID'] > 0) {
                $recipientData = \CUser::GetByID($billingData['UF_AUTHOR_ID'])->Fetch();
            }

            // Для биллинга получатель - это сам пользователь
            $userEmail = $recipientData['EMAIL'] ?? $recipientData->get('EMAIL');

            if (!$userEmail)  throw new NotificationException("Ошибка при отправки уведомлений о пополнений балланса");


            $result = \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME' => 'BILLING_NOTIFICATION',
                'LID' => SITE_ID,
                'C_FIELDS' => [
                    'EMAIL' => $userEmail,
                    'OPERATION' => $billingData['UF_OPERATION'],
                    'VALUE' => $billingData['UF_VALUE'],
                    'DATE_ADD' => $billingData['UF_PUBLISH_DATE']->format("d.m.Y"),
                ],
                "DUPLICATE"=> "N"
            ]);

            if (!$result->isSuccess()) {
                throw new NotificationException(implode(', ', $result->getErrorMessages()));
            }

            /*
            AddMessage2Log(print_r([
                'EMAIL' => $userEmail,
                'OPERATION' => $billingData['UF_OPERATION'],
                'VALUE' => $billingData['UF_VALUE'],
                'DATE_ADD' => $billingData['UF_PUBLISH_DATE']->format("d.m.Y"),
            ],true), "my_module_id");
*/


        } catch (NotificationException $e) {
            $this->logError($e->getMessage());
        }
    }

    /**
     * Логирование ошибок
     */
    private function logError(string $message): void
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_errors.log',
            date('[Y-m-d H:i:s] ') . $message . "\n",
            FILE_APPEND
        );
    }
}