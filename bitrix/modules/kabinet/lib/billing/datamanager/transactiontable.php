<?php
namespace Bitrix\Kabinet\billing\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\FloatField,
    Bitrix\Main\ORM\Fields\IntegerField;

Loc::loadMessages(__FILE__);

/**
 * Class TransactionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SUM double optional default 0
 * <li> STATUS int optional default 0
 * <li> DATE_OPERATION datetime mandatory
 * <li> BILING_ID int mandatory
 * <li> USER_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class TransactionTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_transaction';
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
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_ID_FIELD')
                ]
            ),
            new FloatField(
                'SUM',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_SUM_FIELD')
                ]
            ),
            new IntegerField(
                'STATUS',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_STATUS_FIELD')
                ]
            ),
            new DatetimeField(
                'DATE_OPERATION',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_DATE_OPERATION_FIELD')
                ]
            ),
            new IntegerField(
                'BILING_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_BILING_ID_FIELD')
                ]
            ),
            new IntegerField(
                'USER_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TRANSACTION_ENTITY_USER_ID_FIELD')
                ]
            ),
        ];
    }
}