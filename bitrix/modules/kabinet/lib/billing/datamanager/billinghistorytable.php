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
 * Class BillinghistoryTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_ACTIVE int optional
 * <li> UF_AUTHOR_ID int optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_BILLING_ID int optional
 * <li> UF_USER_EDIT_ID int optional
 * <li> UF_EXT_KEY text optional
 * <li> UF_OPERATION text optional
 * <li> UF_PROJECT_ID int optional
 * <li> UF_TASK_ID int optional
 * <li> UF_QUEUE_ID int optional
 * <li> UF_VALUE double optional
 * <li> UF_PROJECT text optional
 * <li> UF_USER_EDIT text optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class BillinghistoryTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_kabinet_billinghistory';
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
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_ACTIVE',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_ACTIVE_FIELD')
				]
			),
			new IntegerField(
				'UF_AUTHOR_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_AUTHOR_ID_FIELD')
				]
			),
			new DatetimeField(
				'UF_PUBLISH_DATE',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_PUBLISH_DATE_FIELD')
				]
			),
			new IntegerField(
				'UF_BILLING_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_BILLING_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_USER_EDIT_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_USER_EDIT_ID_FIELD')
				]
			),
			new TextField(
				'UF_EXT_KEY',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_EXT_KEY_FIELD')
				]
			),
			new TextField(
				'UF_OPERATION',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_OPERATION_FIELD')
				]
			),
			new IntegerField(
				'UF_PROJECT_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_PROJECT_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_TASK_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_TASK_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_QUEUE_ID',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_QUEUE_ID_FIELD')
				]
			),
            new FloatField(
                'UF_VALUE',
                [
                    'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_VALUE_FIELD')
                ]
            ),
			new TextField(
				'UF_PROJECT',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_PROJECT_FIELD')
				]
			),
			new TextField(
				'UF_USER_EDIT',
				[
					'title' => Loc::getMessage('BILLINGHISTORY_ENTITY_UF_USER_EDIT_FIELD')
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