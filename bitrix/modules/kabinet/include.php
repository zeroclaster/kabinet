<?
IncludeModuleLangFile(__FILE__);

Bitrix\Main\Loader::registerAutoloadClasses(
	"kabinet",
	array(
        'PHelp' => 'class/general/phelp.php',
        'KContainer' => 'class/general/kcontainer.php',
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


// HL Block installed alias consts
// exp: $HLBClass = (\KContainer::getInstance())->get('PROJECTSINFO_HL');
define("BRIEF_HL", "BRIEF_HL");
define("PROJECTSINFO_HL", "PROJECTSINFO_HL");
define("PROJECTSDETAILS_HL", "PROJECTSDETAILS_HL");
define("TARGETAUDIENCE_HL", "TARGETAUDIENCE_HL");
define("TASK_HL", "TASK_HL");
define("FULF_HL", "FULF_HL");
define("CONTRACT_HL", "CONTRACT_HL");
define("BANKDATE_HL", "BANKDATE_HL");
define("HELP_HL", "HELP_HL");         // Помощь на странице
define("LMESSANGER_HL", "LMESSANGER_HL");
define("BILLING_HL", "BILLING_HL");
define("BILLINGHISTORY_HL", "BILLINGHISTORY_HL");
define("ARCHIVEFULFI_HL", "ARCHIVEFULFI_HL");
define("ARCHIVELMESS_HL", "ARCHIVELMESS_HL");
define("ARCHIVETASK_HL", "ARCHIVETASK_HL");

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

	if (
	    \CSite::InDir('/kabinet/') && (!$USER || !$USER->IsAuthorized())
    )
	    LocalRedirect("/login/");

    if(\PHelp::isAdmin())
        $boot = new \Bitrix\Kabinet\Bootadmin;
    else
        $boot = new \Bitrix\Kabinet\Boot;

    $boot->Init();
    $boot->Start();

}, 60);




