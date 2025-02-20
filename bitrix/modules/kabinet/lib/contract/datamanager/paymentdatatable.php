<?php
namespace Bitrix\Kabinet\contract\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class PaymentdataTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_NAME text optional
 * <li> UF_BIK text optional
 * <li> UF_CH_ACCOUNT text optional
 * <li> UF_CORR_CHECK text optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_SORT int optional
 * <li> UF_AUTHOR_ID int optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class PaymentdataTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_paymentdata';
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
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_NAME',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_NAME_FIELD')
                ]
            ),
            new TextField(
                'UF_BIK',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_BIK_FIELD')
                ]
            ),
            new TextField(
                'UF_CH_ACCOUNT',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_CH_ACCOUNT_FIELD')
                ]
            ),
            new TextField(
                'UF_CORR_CHECK',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_CORR_CHECK_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_PUBLISH_DATE',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_PUBLISH_DATE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_SORT',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_SORT_FIELD')
                ]
            ),
            new IntegerField(
                'UF_AUTHOR_ID',
                [
                    'title' => Loc::getMessage('PAYMENTDATA_ENTITY_UF_AUTHOR_ID_FIELD')
                ]
            ),
        ];
    }
}