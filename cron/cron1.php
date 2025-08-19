<?
use Bitrix\Main\Entity,
    Bitrix\Main\Application,
    Bitrix\Main\EventManager,
    Bitrix\Main\EventResult,
    Bitrix\Main\SystemException;

use \Bitrix\Kabinet\DateTime;
//use \Bitrix\Main\Type\DateTime;

/*
 * Используется для тестирования
 * TESTDATE - задает новую дату для new \Bitrix\Kabinet\DateTime()
 * bitrix/modules/kabinet/lib/datetime.php в public function __construct
 */
define("TESTDATE", "06.07.2024");


$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

// Нужно что бы запустился модуль CModule::IncludeModule('kabinet');
define("KABINET_SCRIPT",true);

// Если true то выполняется как из консоли (как крон)
define("DEBUGPARS", true);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
//define("CRON_TIME_LIMIT", 36000);
define("CRON_TIME_LIMIT", 2400); // 40 мин.
// for debug
//define("CRON_TIME_LIMIT", 60); // 1 мин.

define("TIMERSTART", time());

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Login parser user
global $USER;
if (!is_object($USER)) $USER = new CUser;
//$arAuthResult = $USER->Login("yandexmetrica@wsem.ru", "123456");
$USER->Authorize(443);

(\KContainer::getInstance())->setArgs(function(){
			global $USER;
			if ($USER && $USER->IsAuthorized()) {
				$object = \Bitrix\Kabinet\Usertable::createObject();
				$object->set('ID',0);
				return $object;
			}else
				return false;
		},'user');


//@set_time_limit(86400);	// 24 часа
//@set_time_limit(60);
@ignore_user_abort(true);

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);


class utilCron1{
    static public function addlog($message){
        if (empty($message)) return;
        $message = $message . date("d.m.Y H:i:s");
        \Bitrix\Main\Diag\Debug::writeToFile($message,"","/cron/logs/queue.log");
    }
}


$context = Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();


$Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
$taskAutoRun = \Bitrix\Kabinet\task\Autorun::getInstance();

//----------------------------------------------------------------------------------------------------------------

EventManager::getInstance()->addEventHandler('kabinet','OnBeforeStartStage',function(\Bitrix\Main\Event $event){
    extract($event->getParameters());
    \utilCron1::addlog("Запущена стадия ". $title." id:".$id);
});
//----------------------------------------------------------------------------------------------------------------

if (!$server->getRequestMethod() || DEBUGPARS)
{
    try {
        \utilCron1::addlog('---------------------------------------------------------------------------------');
        \utilCron1::addlog('Запуск крона выполнения задачи');

        $Queue->run();
        $taskAutoRun->run();

    } catch (\Exception $e) {

        //var_dump($e->getMessage());

        if ($e->getCode() == 100){
            // Вышло отведенное время
        }elseif($e->getCode() == 200){
            \utilCron1::addlog('Вышло отведенное время');
        }
        else{
            \utilCron1::addlog("Что-то пошло не так! ".$e->getMessage());
        }

    }
}

\utilCron1::addlog('Завершение крона');




