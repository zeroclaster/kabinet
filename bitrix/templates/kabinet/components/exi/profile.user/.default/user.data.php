<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);

/*
* Используется в \bitrix\php_interface\init.php
* необходим что бы подключать модуль кабинета CModule::IncludeModule('kabinet');
*/
define("KABINET_SCRIPT",true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/javascript; charset=utf-8');

//@set_time_limit(86400);	// 24 часа
//@ignore_user_abort(true);

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$dataArray = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Client')->getData();
$currentUser = $dataArray[0];
//echo "<pre>";
//var_dump($currentUser);
//echo "</pre>";

$user_state = CUtil::PhpToJSObject($currentUser, false, true);
?>
    const userStoreData = <?=$user_state?>;
    const  userStore = BX.Vue3.Pinia.defineStore('userfield', {
    state: () => ({datauser:userStoreData}),
    });
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");