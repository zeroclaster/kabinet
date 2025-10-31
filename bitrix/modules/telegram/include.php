<?php

use Bitrix\Kabinet\exceptions\MessangerException;

\Bitrix\Main\Loader::registerAutoLoadClasses("telegram", array(
    "Bitrix\\telegram\Exceptions\TelegramException" => "lib/exceptions/exceptions.php",
    "Bitrix\\telegram\Exceptions\TelegramAuthException" => "lib/exceptions/exceptions.php",
    "Bitrix\\telegram\Exceptions\TelegramMiddlewareException" => "lib/exceptions/exceptions.php",
    "Bitrix\\telegram\Exceptions\NotificationException" => "lib/exceptions/exceptions.php",
));


AddEventHandler("", "\Lmessanger::OnAfterAdd", function ($id, $primary, $fields, $object) {

    $compositeTransport = new \Bitrix\telegram\notificationtransport\Compositetransport();
    $compositeTransport->addTransport(new \Bitrix\telegram\notificationtransport\Telegramtransport());
    $handler = new \Bitrix\telegram\Notificationhandler($compositeTransport);
    // Добавление кастомного правила
    $handler->addRule(new \Bitrix\telegram\notificationrule\Userpreferencerule());
    $handler->handleMessageAdd($id);

});


