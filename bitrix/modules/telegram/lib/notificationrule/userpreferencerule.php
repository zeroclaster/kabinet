<?php
namespace Bitrix\telegram\notificationrule;

use Bitrix\telegram\Abstractnotificationrule;
use Bitrix\telegram\exceptions\TelegramException;

class Userpreferencerule extends Abstractnotificationrule {
    public function shouldSend(array $messageData,$recipientData): bool {


        $UF_AUTHOR_ID = $messageData["UF_AUTHOR_ID"];
        $result_intersect = array_intersect(array(REGISTRATED), \CUser::GetUserGroup($UF_AUTHOR_ID));
        if(empty($result_intersect)) return false;

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