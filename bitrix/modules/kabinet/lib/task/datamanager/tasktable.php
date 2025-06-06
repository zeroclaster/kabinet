<?php
namespace Bitrix\Kabinet\task\datamanager;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query,
    Bitrix\Main\ORM\Query\Join,
    Bitrix\Main\ORM\Fields\Relations\OneToMany;


Loc::loadMessages(__FILE__);

/**
 * Class TaskTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_NAME text optional
 * <li> UF_SORT int optional
 * <li> UF_ACTIVE int optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_AUTHOR_ID int optional
 * <li> UF_USER_EDIT_ID int optional
 * <li> UF_EXT_KEY int optional
 * <li> UF_PROJECT_ID int optional
 * <li> UF_PRODUKT_ID int optional
 * <li> UF_CYCLICALITY int optional
 * <li> UF_DATE_M_LAUNCH int optional
 * <li> UF_NUMBER_STARTS int optional
 * <li> UF_DATE_COMPLETION datetime optional
 * <li> UF_REPORTING int optional
 * <li> UF_PHOTO text optional
 * <li> UF_MANAGER_ID int optional
 * <li> UF_COMMENT text optional
 * <li> UF_TARGET_SITE text optional
 * <li> UF_COORDINATION int optional
 * <li> UF_STATUS int optional
 * <li> UF_RUN_DATE datetime optional
 * <li> UF_JUSTFIELD text optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class TaskTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_kabinet_task';
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
					'title' => Loc::getMessage('TASK_ENTITY_ID_FIELD')
				]
			),
			new TextField(
				'UF_NAME',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_NAME_FIELD')
				]
			),
			new IntegerField(
				'UF_SORT',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_SORT_FIELD')
				]
			),
			new IntegerField(
				'UF_ACTIVE',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_ACTIVE_FIELD')
				]
			),
			new DatetimeField(
				'UF_PUBLISH_DATE',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_PUBLISH_DATE_FIELD')
				]
			),
			new IntegerField(
				'UF_AUTHOR_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_AUTHOR_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_USER_EDIT_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_USER_EDIT_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_EXT_KEY',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_EXT_KEY_FIELD')
				]
			),
			new IntegerField(
				'UF_PROJECT_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_PROJECT_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_PRODUKT_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_PRODUKT_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_CYCLICALITY',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_CYCLICALITY_FIELD')
				]
			),
			new IntegerField(
				'UF_DATE_M_LAUNCH',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_DATE_M_LAUNCH_FIELD')
				]
			),
			new IntegerField(
				'UF_NUMBER_STARTS',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_NUMBER_STARTS_FIELD')
				]
			),
			new DatetimeField(
				'UF_DATE_COMPLETION',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_DATE_COMPLETION_FIELD')
				]
			),
			new IntegerField(
				'UF_REPORTING',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_REPORTING_FIELD')
				]
			),
			new TextField(
				'UF_PHOTO',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_PHOTO_FIELD')
				]
			),
			new IntegerField(
				'UF_MANAGER_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_MANAGER_ID_FIELD')
				]
			),
			new TextField(
				'UF_COMMENT',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_COMMENT_FIELD')
				]
			),
			new TextField(
				'UF_TARGET_SITE',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_TARGET_SITE_FIELD')
				]
			),
			new IntegerField(
				'UF_COORDINATION',
				[
					'title' => Loc::getMessage('TASK_ENTITY_UF_COORDINATION_FIELD')
				]
			),
            new IntegerField(
                'UF_STATUS',
                [
                    'title' => Loc::getMessage('TASK_ENTITY_UF_STATUS_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_RUN_DATE',
                [
                    'title' => Loc::getMessage('TASK_ENTITY_UF_RUN_DATE_FIELD')
                ]
            ),
            new TextField(
                'UF_JUSTFIELD',
                [
                    'title' => Loc::getMessage('TASK_ENTITY_UF_JUSTFIELD_FIELD')
                ]
            ),
            (new Reference(
                'PROJECT',
                \Bitrix\Kabinet\project\datamanager\ProjectsTable::class,
                Join::on('this.UF_PROJECT_ID', 'ref.ID')
            ))->configureJoinType('inner'),
            (new OneToMany('FULFILLMENT', \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class, 'FULFILLMENT'))->configureJoinType('inner'),
            (new Reference(
                'USER',
                \Bitrix\Main\UserTable::class,
                Join::on('this.UF_AUTHOR_ID', 'ref.ID')
            ))->configureJoinType('inner'),
		];
	}

    public static function getListActive(array $parameters = array())
    {
        $parameters['filter']['UF_ACTIVE'] = true;
        return parent::getList($parameters);
    }
}