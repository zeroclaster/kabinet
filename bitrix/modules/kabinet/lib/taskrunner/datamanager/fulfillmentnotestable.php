<?php
namespace Bitrix\Kabinet\taskrunner\datamanager;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

/**
 * Class FulfillmentNotesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_FULFILLMENT_ID int mandatory
 * <li> UF_NOTE_TEXT text mandatory
 * <li> UF_CREATED_BY int mandatory
 * <li> UF_CREATED_DATE datetime optional default current datetime
 * <li> UF_MODIFIED_DATE datetime optional
 * <li> UF_NOTE_TYPE int optional default 1
 * <li> UF_ACTIVE int optional default 1
 * <li> UF_IS_PRIVATE int optional default 0
 * <li> UF_PRIORITY int optional default 1
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class FulfillmentNotesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_fulfillment_notes';
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
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_FULFILLMENT_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_FULFILLMENT_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_NOTE_TEXT',
                [
                    'required' => true,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_NOTE_TEXT_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_CREATED_BY',
                [
                    'required' => true,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_CREATED_BY_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_CREATED_DATE',
                [
                    'default' => function()
                    {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_CREATED_DATE_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_MODIFIED_DATE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_MODIFIED_DATE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_NOTE_TYPE',
                [
                    'default' => 1,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_NOTE_TYPE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_ACTIVE',
                [
                    'default' => 1,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_ACTIVE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_IS_PRIVATE',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_IS_PRIVATE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_PRIORITY',
                [
                    'default' => 1,
                    'title' => Loc::getMessage('FULFILLMENT_NOTES_ENTITY_UF_PRIORITY_FIELD'),
                ]
            ),
            // Связь с основной таблицей исполнения
            (new Reference(
                'FULFILLMENT',
                FulfillmentTable::class,
                Join::on('this.UF_FULFILLMENT_ID', 'ref.ID')
            )),

            // Связь с пользователем
            (new Reference(
                'CREATED_BY_USER',
                \Bitrix\Main\UserTable::class,
                Join::on('this.UF_CREATED_BY', 'ref.ID')
            )),
        ];
    }
}