<?php

includeModuleLangFile(__FILE__);
if (class_exists('Kabinet'))
	return;

class Kabinet extends CModule
{
	var $MODULE_ID = 'kabinet';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = getMessage('KABINET_MODULE_NAME');
		$this->MODULE_DESCRIPTION = getMessage('KABINET_MODULE_DESCRIPTION');
	}

	function doInstall()
	{
		global $DB, $APPLICATION;

		$this->installFiles();
		$this->installDB();

		$GLOBALS['APPLICATION']->includeAdminFile(
			getMessage('KABINET_INSTALL_TITLE'),
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/kabinet/install/step1.php'
		);
	}

	function installDB()
	{
		global $DB, $DBType, $APPLICATION;

		$this->errors = false;
		
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kabinet/install/db/".$DBType."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}		
		
		registerModule($this->MODULE_ID);

		return true;
	}

	function installEvents()
	{
		return true;
	}

	function installFiles()
	{
		/*
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/kabinet/install/admin',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
			true, true
		);
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/kabinet/install/images',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/images',
			true, true
		);
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/kabinet/install/themes',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes',
			true, true
		);
		*/

		return true;
	}

	function doUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;

		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->includeAdminFile(
				getMessage('KABINET_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/bitrix/modules/kabinet/install/unstep1.php'
			);
		}
		elseif ($step == 2)
		{
			$this->uninstallDB(array('savedata' => $_REQUEST['savedata']));
			$this->uninstallFiles();
			$APPLICATION->includeAdminFile(
				getMessage('KABINET_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/bitrix/modules/kabinet/install/unstep2.php'
			);
		}
	}

	function uninstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION, $errors;

		$this->errors = false;

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kabinet/install/db/".$DBType."/uninstall.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		unregisterModule($this->MODULE_ID);

		return true;
	}

	function uninstallEvents()
	{
		return true;
	}

	function uninstallFiles()
	{
		return true;
	}

}
