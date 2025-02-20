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
header('Cache-Control: public, max-age=31536000');
header('Pragma: cache');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    //header('HTTP/1.1 304 Not Modified');
    //die();
}
//@set_time_limit(86400);	// 24 часа
//@ignore_user_abort(true);

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);


$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = $sL->get('Kabinet.Project');
$data = $projectManager->catalogData();
$brief_state = CUtil::PhpToJSObject($data, false, true);
?>
const catalogListStoreData = <?=$brief_state?>;
const cataloglistStore = BX.Vue3.Pinia.defineStore('cataloglist', {
state: () => ({
    data3:catalogListStoreData,
    message:{
    error1:'Нельзя выбрать больше максимального количества в месяц',
    error2:'Вы не выбрали количество!',
	error3:'Нельзя выбрать меньше минимального количества в месяц',
    }}),
});
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");