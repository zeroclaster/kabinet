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
//@set_time_limit(86400);	// 24 часа
//@ignore_user_abort(true);

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$billingManager = $sL->get('Kabinet.Billing');
$data = $billingManager->getData();

//echo "<pre>";
//var_dump($data);
//echo "</pre>";

$billing_state = CUtil::PhpToJSObject($data, false, true);
?>
    const billingStoreData = <?=$billing_state?>;
    const  billingStore = BX.Vue3.Pinia.defineStore('billing-Store', {
    state: () => ({
    missingAmount: 0, // Добавляем поле для недостающей суммы
    databilling:billingStoreData
    }),
    actions: {
    // Добавляем action для установки недостающей суммы
    setMissingAmount(amount) {
    if (amount < 1000) amount = 1000;
    this.missingAmount = amount;
    },
    // Очищаем недостающую сумму после пополнения
    clearMissingAmount() {
    this.missingAmount = 0;
    }
    }
    });
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
