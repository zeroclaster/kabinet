<?
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//@set_time_limit(86400);	// 24 часа
@ignore_user_abort(true);

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$result = \Bitrix\Main\Mail\Event::send([
    'EVENT_NAME' => 'KABINET_NOTIFICATION',
    'LID' => SITE_ID,
    'C_FIELDS' => [
        'EMAIL' => "help@exiterra.ru",
        'MESSAGE' => "1231312",
    ],
    "DUPLICATE"=> "N"
]);

//
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");