<?php
namespace Bitrix\telegram;

use Bitrix\telegram\contracts\Notificationruleinterface;

abstract class Abstractnotificationrule implements Notificationruleinterface {
    protected $nextRule;

    public function setNext(Notificationruleinterface $rule): Notificationruleinterface {
        $this->nextRule = $rule;
        return $rule;
    }

    public function shouldSend(array $messageData, $recipientData): bool {
        if ($this->nextRule) {
            return $this->nextRule->shouldSend($messageData, $recipientData);
        }
        return true;
    }
}
