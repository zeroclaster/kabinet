<?php
namespace Bitrix\telegram;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class HistoryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> PERIOD string(50) mandatory
 * <li> PERIOD_DATE date mandatory
 * <li> DATE_SENT datetime mandatory
 * <li> IS_RECOVERY bool ('N', 'Y') optional default 'N'
 * </ul>
 *
 * @package Bitrix\Notification
 **/

class HistoryTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_notification_history';
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
                    'title' => Loc::getMessage('HISTORY_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'USER_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('HISTORY_ENTITY_USER_ID_FIELD'),
                ]
            ),
            new StringField(
                'PERIOD',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 50),
                        ];
                    },
                    'title' => Loc::getMessage('HISTORY_ENTITY_PERIOD_FIELD'),
                ]
            ),
            new DateField(
                'PERIOD_DATE',
                [
                    'required' => true,
                    'title' => Loc::getMessage('HISTORY_ENTITY_PERIOD_DATE_FIELD'),
                ]
            ),
            new DatetimeField(
                'DATE_SENT',
                [
                    'required' => true,
                    'title' => Loc::getMessage('HISTORY_ENTITY_DATE_SENT_FIELD'),
                ]
            ),
            new BooleanField(
                'IS_RECOVERY',
                [
                    'values' => ['N', 'Y'],
                    'default' => 'N',
                    'title' => Loc::getMessage('HISTORY_ENTITY_IS_RECOVERY_FIELD'),
                ]
            ),
        ];
    }
}