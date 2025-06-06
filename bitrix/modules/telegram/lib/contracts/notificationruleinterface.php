<?php
namespace Bitrix\telegram\contracts;

interface Notificationruleinterface {
    public function shouldSend(array $messageData, array $recipientData): bool;
}
