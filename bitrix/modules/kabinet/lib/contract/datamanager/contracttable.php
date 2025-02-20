<?php
namespace Bitrix\Kabinet\contract\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class ContractTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_NAME text optional
 * <li> UF_PUBLISH_DATE datetime optional
 * <li> UF_SORT int optional
 * <li> UF_UR_ADDRESS text optional
 * <li> UF_INN text optional
 * <li> UF_KPP text optional
 * <li> UF_OGRN text optional
 * <li> UF_MAILN_ADDRESS text optional
 * <li> UF_FIO text optional
 * <li> UF_ACTS text optional
 * <li> UF_AUTHOR_ID int optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class ContractTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_contract';
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
                    'title' => Loc::getMessage('CONTRACT_ENTITY_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_NAME',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_NAME_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_PUBLISH_DATE',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_PUBLISH_DATE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_SORT',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_SORT_FIELD')
                ]
            ),
            new TextField(
                'UF_UR_ADDRESS',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_UR_ADDRESS_FIELD')
                ]
            ),
            new TextField(
                'UF_INN',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_INN_FIELD')
                ]
            ),
            new TextField(
                'UF_KPP',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_KPP_FIELD')
                ]
            ),
            new TextField(
                'UF_OGRN',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_OGRN_FIELD')
                ]
            ),
            new TextField(
                'UF_MAILN_ADDRESS',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_MAILN_ADDRESS_FIELD')
                ]
            ),
            new TextField(
                'UF_FIO',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_FIO_FIELD')
                ]
            ),
            new TextField(
                'UF_ACTS',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_ACTS_FIELD')
                ]
            ),
            new IntegerField(
                'UF_AUTHOR_ID',
                [
                    'title' => Loc::getMessage('CONTRACT_ENTITY_UF_AUTHOR_ID_FIELD')
                ]
            ),
        ];
    }
}