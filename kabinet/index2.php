<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кабинет «Купи-Отзыв»");
?>

<?
if (0) {
// Инициализация
    $compositeTransport = new \Bitrix\telegram\notificationtransport\Compositetransport();
//$compositeTransport->addTransport(new \Bitrix\telegram\notificationtransport\Telegramtransport());
    $compositeTransport->addTransport(new \Bitrix\telegram\notificationtransport\Emailtransport());
    $handler = new \Bitrix\telegram\Notificationhandler($compositeTransport);
// Добавление кастомного правила
    $handler->addRule(new \Bitrix\telegram\notificationrule\Userpreferencerule());
    $handler->handleMessageAdd(206);
}

// Запуск обработки
$sender = new \Bitrix\telegram\Notificationsender();
$sender->execute();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>