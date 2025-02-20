<?define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вход");
?>

<?
LocalRedirect("/kabinet/");
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
