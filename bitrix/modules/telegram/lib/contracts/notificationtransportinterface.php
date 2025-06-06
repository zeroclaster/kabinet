<?php
namespace Bitrix\telegram\contracts;

interface Notificationtransportinterface {
    public function send(array $recipientData, array $messageData, string $message): bool;
}
