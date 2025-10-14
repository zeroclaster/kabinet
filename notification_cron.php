<?
use Bitrix\Main\Entity,
    Bitrix\Main\Application,
    Bitrix\Main\EventManager,
    Bitrix\Main\EventResult,
    Bitrix\Monetization\helper\Gbstorage,
    Bitrix\Main\SystemException,
    Bitrix\Main\Type\DateTime;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/.");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

// Если true то выполняется как из консоли (как крон)
define("DEBUGPARS", true);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
//define("CRON_TIME_LIMIT", 36000);
define("CRON_TIME_LIMIT", 2400); // 40 мин.
// for debug
//define("CRON_TIME_LIMIT", 60); // 1 мин.

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//@set_time_limit(86400);	// 24 часа
//@set_time_limit(60);
@ignore_user_abort(true);


CModule::IncludeModule('kabinet');

// Login parser user
global $USER;
if (!is_object($USER)) $USER = new \CUser;
/*
 * Робот manager1 manager1@manager1.ru
 */
$USER->Authorize(443);
onPrologBootstrape();

// Запуск обработки
$sender = new \Bitrix\telegram\Notificationsender();
$sender->execute();

// Запуск обработки уведомлений об операциях биллинга
//$billingSender = new \Bitrix\telegram\BillingNotificationSender();
//$billingSender->execute();