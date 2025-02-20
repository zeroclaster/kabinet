<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержка");
?>


<?

$billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');

$a = $billing->actualMonthExpenses(5);

var_dump($a);

?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>