<?php
namespace Bitrix\Kabinet;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField;

Loc::loadMessages(__FILE__);

/**
 * Class GroupTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> DATE_ACTIVE_FROM datetime optional
 * <li> DATE_ACTIVE_TO datetime optional
 * </ul>
 *
 * @package Bitrix\User
 **/

class GroupTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_user_group';
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
                'USER_ID',
                [
                    'primary' => true,
                    'title' => Loc::getMessage('GROUP_ENTITY_USER_ID_FIELD')
                ]
            ),
            new IntegerField(
                'GROUP_ID',
                [
                    'primary' => true,
                    'title' => Loc::getMessage('GROUP_ENTITY_GROUP_ID_FIELD')
                ]
            ),
            new DatetimeField(
                'DATE_ACTIVE_FROM',
                [
                    'title' => Loc::getMessage('GROUP_ENTITY_DATE_ACTIVE_FROM_FIELD')
                ]
            ),
            new DatetimeField(
                'DATE_ACTIVE_TO',
                [
                    'title' => Loc::getMessage('GROUP_ENTITY_DATE_ACTIVE_TO_FIELD')
                ]
            ),
        ];
    }
}
