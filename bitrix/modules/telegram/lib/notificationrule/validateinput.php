<?php
namespace Bitrix\telegram\notificationrule;

use Bitrix\Main\SystemException;
use Bitrix\telegram\Abstractnotificationrule;

class Validateinput extends Abstractnotificationrule {
    public function shouldSend(array $messageData, $recipientData): bool {
        if (empty($messageData['UF_MESSAGE_TEXT'])) {
            return false;
        }

        return parent::shouldSend($messageData, $recipientData);
    }
}