<?php
namespace Bitrix\Kabinet\messanger\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class LmessangerTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_SORT int optional
 * <li> UF_ACTIVE int optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_AUTHOR_ID int optional
 * <li> UF_PROJECT_ID int optional
 * <li> UF_TASK_ID int optional
 * <li> UF_QUEUE_ID int optional
 * <li> UF_SUBMESS_ID int optional
 * <li> UF_MESSAGE_TEXT text optional
 * <li> UF_USER_EDIT_ID int optional
 * <li> UF_EXT_KEY text optional
 * <li> UF_UPLOADFILE text optional
 * <li> UF_TYPE int optional
 * <li> UF_STATUS int optional
 * <li> UF_TARGET_USER_ID int optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class LmessangerTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_lmessanger';
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
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_SORT',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_SORT_FIELD')
                ]
            ),
            new IntegerField(
                'UF_ACTIVE',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_ACTIVE_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_PUBLISH_DATE',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_PUBLISH_DATE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_AUTHOR_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_AUTHOR_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_PROJECT_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_PROJECT_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_TASK_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_TASK_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_QUEUE_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_QUEUE_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_SUBMESS_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_SUBMESS_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_MESSAGE_TEXT',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_MESSAGE_TEXT_FIELD')
                ]
            ),
            new IntegerField(
                'UF_USER_EDIT_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_USER_EDIT_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_EXT_KEY',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_EXT_KEY_FIELD')
                ]
            ),
            new TextField(
                'UF_UPLOADFILE',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_UPLOADFILE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_TYPE',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_TYPE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_STATUS',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_STATUS_FIELD')
                ]
            ),
            new IntegerField(
                'UF_TARGET_USER_ID',
                [
                    'title' => Loc::getMessage('LMESSANGER_ENTITY_UF_TARGET_USER_ID_FIELD')
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