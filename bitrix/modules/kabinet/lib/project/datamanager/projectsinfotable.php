<?php
namespace Bitrix\Kabinet\project\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class ProjectsInfoTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_PROJECT_ID int optional
 * <li> UF_PROJECT_GOAL text optional
 * <li> UF_SITE text optional
 * <li> UF_OFFICIAL_NAME text optional
 * <li> UF_REVIEWS_NAME text optional
 * <li> UF_CONTACTS_PUBLIC text optional
 * <li> UF_COMP_PREVIEW_TEXT text optional
 * <li> UF_COMP_DESCRIPTION_TEXT text optional
 * <li> UF_COMP_LOGO int optional
 * <li> UF_ORG_ADDRESS text optional
 * <li> UF_WORKING_HOURS text optional
 * <li> UF_TOPICS_LIST text optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class ProjectsInfoTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_projects_info';
    }
	
    public static function getObjectClass()
    {
        return ProjectsInfo::class;
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
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_PROJECT_ID',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_PROJECT_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_PROJECT_GOAL',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_PROJECT_GOAL_FIELD')
                ]
            ),
            new TextField(
                'UF_SITE',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_SITE_FIELD')
                ]
            ),
            new TextField(
                'UF_OFFICIAL_NAME',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_OFFICIAL_NAME_FIELD')
                ]
            ),
            new TextField(
                'UF_REVIEWS_NAME',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_REVIEWS_NAME_FIELD')
                ]
            ),
            new TextField(
                'UF_CONTACTS_PUBLIC',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_CONTACTS_PUBLIC_FIELD')
                ]
            ),
            new TextField(
                'UF_COMP_PREVIEW_TEXT',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_COMP_PREVIEW_TEXT_FIELD')
                ]
            ),
            new TextField(
                'UF_COMP_DESCRIPTION_TEXT',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_COMP_DESCRIPTION_TEXT_FIELD')
                ]
            ),
            new IntegerField(
                'UF_COMP_LOGO',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_COMP_LOGO_FIELD')
                ]
            ),
            new TextField(
                'UF_ORG_ADDRESS',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_ORG_ADDRESS_FIELD')
                ]
            ),
            new TextField(
                'UF_WORKING_HOURS',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_WORKING_HOURS_FIELD')
                ]
            ),
            new TextField(
                'UF_TOPICS_LIST',
                [
                    'title' => Loc::getMessage('PROJECTS_INFO_ENTITY_UF_TOPICS_LIST_FIELD')
                ]
            ),
        ];
    }
}
