<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержка");
?>


<?

echo "start";
$projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');
//$order_id = $projectManager->addproductNewOrder($fields['id'], $fields['count']);
$projectManager->addproductToOrder(905, 2870, 1);
echo "stop";

?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>