<?php
namespace Bitrix\Kabinet\project\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class ProjectsDetailsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_ABOUT_REVIEW text optional
 * <li> UF_POSITIVE_SIDES text optional
 * <li> UF_MINUSES text optional
 * <li> UF_MINUSES_USER text optional
 * <li> UF_ORDER_PROCESS_USER text optional
 * <li> UF_EXAMPLES_REVIEWS text optional
 * <li> UF_MENTION_REVIEWS text optional
 * <li> UF_KEYWORDS text optional
 * <li> UF_PROJECT_ID int optional
 * <li> UF_ORDER_PROCESS text optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class ProjectsDetailsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_projects_details';
    }
	
    public static function getObjectClass()
    {
        return ProjectsDetails::class;
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
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_ABOUT_REVIEW',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_ABOUT_REVIEW_FIELD')
                ]
            ),
            new TextField(
                'UF_POSITIVE_SIDES',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_POSITIVE_SIDES_FIELD')
                ]
            ),
            new TextField(
                'UF_MINUSES',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_MINUSES_FIELD')
                ]
            ),
            new TextField(
                'UF_MINUSES_USER',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_MINUSES_USER_FIELD')
                ]
            ),
            new TextField(
                'UF_ORDER_PROCESS_USER',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_ORDER_PROCESS_USER_FIELD')
                ]
            ),
            new TextField(
                'UF_EXAMPLES_REVIEWS',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_EXAMPLES_REVIEWS_FIELD')
                ]
            ),
            new TextField(
                'UF_MENTION_REVIEWS',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_MENTION_REVIEWS_FIELD')
                ]
            ),
            new TextField(
                'UF_KEYWORDS',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_KEYWORDS_FIELD')
                ]
            ),
            new IntegerField(
                'UF_PROJECT_ID',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_PROJECT_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_ORDER_PROCESS',
                [
                    'title' => Loc::getMessage('PROJECTS_DETAILS_ENTITY_UF_ORDER_PROCESS_FIELD')
                ]
            ),
        ];
    }
}
