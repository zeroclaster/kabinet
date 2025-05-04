<?
IncludeModuleLangFile(__FILE__);

Bitrix\Main\Loader::registerAutoloadClasses(
	"kabinet",
	array(
        'PHelp' => 'class/general/phelp.php',
        'KContainer' => 'class/general/kcontainer.php',
        'Bitrix\Main\DI\ServiceLoader' => 'class/general/serviceloader.php',
	)
);

// Kabinet Exceptions
\Bitrix\Main\Loader::registerAutoLoadClasses("kabinet", array(
    "Bitrix\Kabinet\Exceptions\TaskException" => "lib/exceptions/exceptions.php",
    "Bitrix\Kabinet\Exceptions\ProjectException" => "lib/exceptions/exceptions.php",
    "Bitrix\Kabinet\Exceptions\FulfiException" => "lib/exceptions/exceptions.php",
    "Bitrix\Kabinet\Exceptions\MessangerException" => "lib/exceptions/exceptions.php",
    "Bitrix\Kabinet\Exceptions\BillingException" => "lib/exceptions/exceptions.php",
    "Bitrix\Kabinet\Exceptions\TestException" => "lib/exceptions/exceptions.php",
));

CModule::IncludeModule('highloadblock');

define("BRIEF", 4);
define("BRIEFFIELDS", 5);
define("BRIEFFIELDSVALUE", 6);
define("PROJECTSINFO", 8);
define("PROJECTSDETAILS", 9);
define("TARGETAUDIENCE", 12);
define("TASK", 13);
define("FULF", 14);             // Исполнение
define("CONTRACT", 16);         // Договор с пользователем
define("BANKDATE", 17);         // Банковские реквизиты
define("HELP", 18);         // Помощь на странице
define("LMESSANGER", 19);
define("BILLING", 20);
define("BILLINGHISTORY", 21);
define("ARCHIVEFULFI", 23);
define("ARCHIVELMESS", 24);
define("ARCHIVETASK", 25);


$config = [
    'CHART'=> [
        'ALLOWED_EXTENSIONS'=>'gif|png|jpe?g|txt|doc?x|xls?m|zip|rar',
        'UPLOAD_SIZE_LIMIT' => 5000000, // максимальный размер загружаемых файлов в байтах
    ],
    'USER' =>[
        'photo_default' => '/bitrix/templates/kabinet/assets/images/users/user_nofoto.jpeg',
    ],
];

(\KContainer::getInstance())->maked($config,'config');

// Запуск модуля!
AddEventHandler("main", "OnProlog", function(){
	global $USER;

    if (!$USER->IsAuthorized()) throw new \Bitrix\Main\SystemException("Сritical error! Registered users only.");

    $context = \PHelp::isAdmin() ? 'admin' : 'user';
    $loader = new   \Bitrix\Main\DI\ServiceLoader('bitrix/modules/kabinet/lib/services.php',$context);
    $loader->register();

/*
    $taskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
    var_dump($taskManager);
    exit();
*/

	if (
	    \CSite::InDir('/kabinet/') && (!$USER || !$USER->IsAuthorized())
    )
	    LocalRedirect("/login/");

    $locator = \Bitrix\Main\DI\ServiceLocator::getInstance();
    $bootService = \PHelp::isAdmin()
        ? $locator->get('boot.admin')
        : $locator->get('boot.user');

    $bootService->run();

}, 60);




