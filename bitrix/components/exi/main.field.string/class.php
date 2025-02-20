<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\StringType;

/**
 * Class StringUfComponent
 */
class StringUfComponent extends BaseUfComponent
{
	public const TEMPLATE_NAME_DEFAULT2 = 'brief';

	protected static function getUserTypeId(): string
	{
		return StringType::USER_TYPE_ID;
	}
}