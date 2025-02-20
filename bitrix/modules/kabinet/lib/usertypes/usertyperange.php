<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeDouble
 * @deprecated deprecated since main 20.0.700
 */

class CUserTypeRange extends TypeBase
{
	const USER_TYPE_ID = RangeType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return RangeType::getUserTypeDescription();
	}

	function getSettingsHtml($userField, $additionalSettings, $varsFromForm)
	{
		return RangeType::renderSettings($userField, $additionalSettings, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalSettings)
	{
		return RangeType::renderEditForm($userField, $additionalSettings);
	}

	function getFilterHtml($userField, $additionalSettings)
	{
		return RangeType::renderFilter($userField, $additionalSettings);
	}

	function getAdminListViewHtml($userField, $additionalSettings)
	{
		return RangeType::renderAdminListView($userField, $additionalSettings);
	}

	function getAdminListEditHtml($userField, $additionalSettings)
	{
		return RangeType::renderAdminListEdit($userField, $additionalSettings);
	}

	public static function getPublicView($userField, $arAdditionalParameters = array())
	{
		return RangeType::renderView($userField, $arAdditionalParameters);
	}

	public function getPublicEdit($userField, $arAdditionalParameters = array())
	{
		return RangeType::renderEdit($userField, $arAdditionalParameters);
	}

	public static function getDbColumnType($userField)
	{
		return RangeType::getDbColumnType();
	}

	function getFilterData($userField, $additionalSettings)
	{
		return RangeType::getFilterData($userField, $additionalSettings);
	}

	function prepareSettings($userField)
	{
		return RangeType::prepareSettings($userField);
	}

	function checkFields($userField, $value)
	{
		return RangeType::checkFields($userField, $value);
	}

	function onBeforeSave($userField, $value)
	{
		return RangeType::onBeforeSave($userField, $value);
	}

	function onSearchIndex($userField)
	{
		return RangeType::onSearchIndex($userField);
	}

}
