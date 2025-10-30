<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage('FORM_MAKER_NAME'),
	"DESCRIPTION" => Loc::getMessage('FORM_MAKER_DESCRIPTION'),
	"ICON" => '/images/icon.gif',
	"SORT" => 20,
	"PATH" => array(
		"ID" => 'content',
		"SORT" => 10,
		"CHILD" => array(
			"ID" => 'hlblock',
			"NAME" => Loc::getMessage('FORM_MAKER_GROUP'),
			"SORT" => 10,
			"CHILD" => array(
				"ID" => "public_list",
			),
		)
	),
);

?>