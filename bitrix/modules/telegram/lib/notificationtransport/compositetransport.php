<?php
namespace Bitrix\telegram\notificationtransport;

use Bitrix\telegram\contracts\Notificationtransportinterface;

class Compositetransport implements Notificationtransportinterface {
    private $transports = [];

    public function addTransport(Notificationtransportinterface $transport): void {
        $this->transports[] = $transport;
    }

    public function send($recipientData, array $messageData, string $message): bool {
        $results = [];
        foreach ($this->transports as $transport) {
            $results[] = $transport->send($recipientData, $messageData, $message);
        }
        return in_array(true, $results, true);
    }
}
