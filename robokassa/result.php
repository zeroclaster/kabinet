<?
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
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

if (
    !empty($_REQUEST["OutSum"]) &&
    !empty($_REQUEST["InvId"]) &&
    !empty($_REQUEST["SignatureValue"])
) {
                        $result = new \Bitrix\Kabinet\billing\paysystem\robokassa\Result2();

                        // For Test!!!
                        //print_r($result->makeCRC());
                        if ($result->isSuccess()) {
                            echo "Ваш баланс успешно пополнен!";

                        }else{
                            $err = $result->getErrors();
                            echo "{$err}";
                        }
}

