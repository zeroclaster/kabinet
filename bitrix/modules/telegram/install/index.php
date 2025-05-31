<?

IncludeModuleLangFile(__FILE__);
use \Bitrix\Main\ModuleManager;

Class telegram extends CModule
{

	const MODULE_ID = "telegram";
    var $MODULE_ID = "telegram";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $errors;

    function __construct()
    {
        //$arModuleVersion = array();
        $this->MODULE_VERSION = "1.0.0";
        $this->MODULE_VERSION_DATE = "29.05.2025";
        $this->MODULE_NAME = "telegram";
        $this->MODULE_DESCRIPTION = "Модуль telegram";
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
		$this->installDB();
		
        \Bitrix\Main\ModuleManager::RegisterModule("telegram");
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        \Bitrix\Main\ModuleManager::UnRegisterModule("telegram");
        return true;
    }

    function InstallDB()
    {
        global $DB;

        $this->errors = false;
		
		/*
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/telegram/install/db/".$DBType."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}
		*/		
		        
        return $this->errors;
    }

    function UnInstallDB()
    {
        global $DB;

        $this->errors = false;
        
		/*
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/telegram/install/db/".$DBType."/uninstall.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}
		*/
		
        return $this->errors;
    }

   	function InstallEvents()
	{
	
		return true;
	}

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }
}