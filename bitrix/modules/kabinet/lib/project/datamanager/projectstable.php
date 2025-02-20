<?php
namespace Bitrix\Kabinet\project\datamanager;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class ProjectsTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_NAME text optional
 * <li> UF_SORT int optional
 * <li> UF_ACTIVE int optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_AUTHOR_ID int optional
 * <li> UF_STATUS int optional
 * <li> UF_EXT_KEY int optional
 * <li> UF_USER_EDIT_ID int optional
 * <li> UF_ADDITIONAL_WISHES text optional
 * <li> UF_ORDER_ID int optional
 * <li> UF_PRODUKT_ID text optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class ProjectsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_kabinet_projects';
	}
	
    public static function getObjectClass()
    {
        return Projects::class;
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
					'title' => Loc::getMessage('PROJECTS_ENTITY_ID_FIELD')
				]
			),
			new TextField(
				'UF_NAME',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_NAME_FIELD')
				]
			),
			new IntegerField(
				'UF_SORT',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_SORT_FIELD')
				]
			),
			new IntegerField(
				'UF_ACTIVE',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_ACTIVE_FIELD')
				]
			),
			new DatetimeField(
				'UF_PUBLISH_DATE',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_PUBLISH_DATE_FIELD')
				]
			),
			new IntegerField(
				'UF_AUTHOR_ID',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_AUTHOR_ID_FIELD')
				]
			),
			new IntegerField(
				'UF_STATUS',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_STATUS_FIELD')
				]
			),
			new IntegerField(
				'UF_EXT_KEY',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_EXT_KEY_FIELD')
				]
			),
			new IntegerField(
				'UF_USER_EDIT_ID',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_USER_EDIT_ID_FIELD')
				]
			),
            new TextField(
                'UF_ADDITIONAL_WISHES',
                [
                    'title' => Loc::getMessage('PROJECTS_ENTITY_UF_ADDITIONAL_WISHES_FIELD')
                ]
            ),
			new IntegerField(
				'UF_ORDER_ID',
				[
					'title' => Loc::getMessage('PROJECTS_ENTITY_UF_ORDER_ID_FIELD')
				]
			),
            new TextField(
                'UF_PRODUKT_ID',
                [
                    'title' => Loc::getMessage('PROJECTS_ENTITY_UF_PRODUKT_ID_FIELD')
                ]
            ),
            (new Reference(
                'USER',
                \Bitrix\Kabinet\UserTable::class,
                Join::on('this.UF_AUTHOR_ID', 'ref.ID')
            ))->configureJoinType('inner'),
			(new Reference(
                'INFO',
                ProjectsInfoTable::class,
                Join::on('this.ID', 'ref.UF_PROJECT_ID')
            ))->configureJoinType('inner'),
			(new Reference(
                'DETAILS',
                ProjectsDetailsTable::class,
                Join::on('this.ID', 'ref.UF_PROJECT_ID')
            ))->configureJoinType('inner'),	
			(new Reference(
                'TARGETAUDIENCE',
                TargetAudienceTable::class,
                Join::on('this.ID', 'ref.UF_PROJECT_ID')
            ))->configureJoinType('inner'),				
		];
	}
	
    public static function getListActive(array $parameters = array())
    {
        $parameters['filter']['UF_ACTIVE'] = true;
        return parent::getList($parameters);
    }	
}