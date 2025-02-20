<?php
namespace Bitrix\Kabinet\billing\datamanager;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\FloatField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class BillingTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_AUTHOR_ID int optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_EDIT_DATE datetime optional
 * <li> UF_ACTIVE int optional
 * <li> UF_EXT_KEY text optional
 * <li> UF_USER_EDIT_ID int optional
 * <li> UF_VALUE double optional
 * <li> UF_SORT int optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class BillingTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_kabinet_billing';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('BILLING_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_AUTHOR_ID',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_AUTHOR_ID_FIELD')
				]
			),
			new DatetimeField(
				'UF_PUBLISH_DATE',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_PUBLISH_DATE_FIELD')
				]
			),
			new DatetimeField(
				'UF_EDIT_DATE',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_EDIT_DATE_FIELD')
				]
			),
			new IntegerField(
				'UF_ACTIVE',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_ACTIVE_FIELD')
				]
			),
			new TextField(
				'UF_EXT_KEY',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_EXT_KEY_FIELD')
				]
			),
			new IntegerField(
				'UF_USER_EDIT_ID',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_USER_EDIT_ID_FIELD')
				]
			),
			new FloatField(
				'UF_VALUE',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_VALUE_FIELD')
				]
			),
			new IntegerField(
				'UF_SORT',
				[
					'title' => Loc::getMessage('BILLING_ENTITY_UF_SORT_FIELD')
				]
			),
		];
	}
	
    public static function getListActive(array $parameters = array())
    {
        $parameters['filter']['UF_ACTIVE'] = true;
        return parent::getList($parameters);
    }
}