<?php
namespace Bitrix\telegram\notificationrule;

use Bitrix\telegram\Abstractnotificationrule;

class Userpreferencerule extends Abstractnotificationrule {
    public function shouldSend(array $messageData,$recipientData): bool {

        /*
        if ($recipientData['TASK']) {
            $TASK = $recipientData->get('TASK');
            $user = $TASK->get("USER");
            if  ($user && $user->has('UF_NOTIFICATIONS_DISABLED') && $user['UF_NOTIFICATIONS_DISABLED']) {
                return false;
            }
        }

        if ($recipientData['USER']) {
            $user = $recipientData->get("USER");
            if ($user->has('UF_NOTIFICATIONS_DISABLED') && $user['UF_NOTIFICATIONS_DISABLED']) {
                return false;
            }
        }

        if  ($user->has('UF_NOTIFICATIONS_DISABLED') && $user['UF_NOTIFICATIONS_DISABLED']) {
            return false;
        }
        */

        return parent::shouldSend($messageData, $recipientData);
    }
}