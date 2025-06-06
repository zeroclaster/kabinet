<?php
namespace Bitrix\Kabinet\taskrunner\datamanager;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\FloatField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query,
    Bitrix\Main\ORM\Query\Join,
    Bitrix\Main\ORM\Fields\Relations\OneToMany;

Loc::loadMessages(__FILE__);

/**
 * Class FulfillmentTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_TASK_ID int optional
 * <li> UF_ELEMENT_TYPE text optional
 * <li> UF_ACTIVE int optional default 1
 * <li> UF_STATUS int optional default 0
 * <li> UF_HISTORYCHANGE string(2048) optional
 * <li> UF_PLANNE_DATE datetime optional
 * <li> UF_LINK string(2048) optional
 * <li> UF_LINK_PRODUCT string(2048) optional
 * <li> UF_ACTUAL_DATE datetime optional
 * <li> UF_SITE_SETUP text optional
 * <li> UF_REPORT_LINK string(2048) optional
 * <li> UF_REPORT_SCREEN string(2048) optional
 * <li> UF_REPORT_FILE string(2048) optional
 * <li> UF_REPORT_TEXT string(2048) optional
 * <li> UF_REVIEW_TEXT text optional
 * <li> UF_LINKINTEXT text optional
 * <li> UF_PIC_REVIEW text optional
 * <li> UF_COMMENT text optional
 * <li> UF_OPERATION text optional
 * <li> UF_CREATE_DATE datetime optional
 * <li> UF_RUN_DATE datetime optional
 * <li> UF_MONEY_RESERVE double optional
 * <li> UF_HITCH int optional
 * <li> UF_NUMBER_STARTS int optional
 * <li> UF_DATE_COMPLETION datetime optional
 * <li> UF_EXT_KEY int optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class FulfillmentTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_fulfillment';
    }

    public static function getObjectClass()
    {
        return Fulfillment::class;
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
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_TASK_ID',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_TASK_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_ELEMENT_TYPE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_ELEMENT_TYPE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_ACTIVE',
                [
                    'default' => 1,
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_ACTIVE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_STATUS',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_STATUS_FIELD')
                ]
            ),
            new StringField(
                'UF_HISTORYCHANGE',
                [
                    'validation' => [__CLASS__, 'validateUfHistorychange'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_HISTORYCHANGE_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_PLANNE_DATE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_PLANNE_DATE_FIELD')
                ]
            ),
            new StringField(
                'UF_LINK',
                [
                    'validation' => [__CLASS__, 'validateUfLink'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_LINK_FIELD')
                ]
            ),
            new StringField(
                'UF_LINK_PRODUCT',
                [
                    'validation' => [__CLASS__, 'validateUfLinkProduct'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_LINK_PRODUCT_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_ACTUAL_DATE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_ACTUAL_DATE_FIELD')
                ]
            ),
            new TextField(
                'UF_SITE_SETUP',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_SITE_SETUP_FIELD')
                ]
            ),
            new StringField(
                'UF_REPORT_LINK',
                [
                    'validation' => [__CLASS__, 'validateUfReportLink'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_REPORT_LINK_FIELD')
                ]
            ),
            new StringField(
                'UF_REPORT_SCREEN',
                [
                    'validation' => [__CLASS__, 'validateUfReportScreen'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_REPORT_SCREEN_FIELD')
                ]
            ),
            new StringField(
                'UF_REPORT_FILE',
                [
                    'validation' => [__CLASS__, 'validateUfReportFile'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_REPORT_FILE_FIELD')
                ]
            ),
            new StringField(
                'UF_REPORT_TEXT',
                [
                    'validation' => [__CLASS__, 'validateUfReportText'],
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_REPORT_TEXT_FIELD')
                ]
            ),
            new TextField(
                'UF_REVIEW_TEXT',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_REVIEW_TEXT_FIELD')
                ]
            ),
            new TextField(
                'UF_LINKINTEXT',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_LINKINTEXT_FIELD')
                ]
            ),
            new TextField(
                'UF_PIC_REVIEW',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_PIC_REVIEW_FIELD')
                ]
            ),
            new TextField(
                'UF_COMMENT',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_COMMENT_FIELD')
                ]
            ),
            new TextField(
                'UF_OPERATION',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_OPERATION_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_CREATE_DATE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_CREATE_DATE_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_RUN_DATE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_RUN_DATE_FIELD')
                ]
            ),
            new FloatField(
                'UF_MONEY_RESERVE',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_MONEY_RESERVE_FIELD')
                ]
            ),
            new IntegerField(
                'UF_HITCH',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_HITCH_FIELD')
                ]
            ),
            new IntegerField(
                'UF_NUMBER_STARTS',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_NUMBER_STARTS_FIELD')
                ]
            ),
            new DatetimeField(
                'UF_DATE_COMPLETION',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_DATE_COMPLETION_FIELD')
                ]
            ),
            new IntegerField(
                'UF_EXT_KEY',
                [
                    'title' => Loc::getMessage('FULFILLMENT_ENTITY_UF_EXT_KEY_FIELD')
                ]
            ),
            (new Reference(
                'TASK',
                \Bitrix\Kabinet\task\datamanager\TaskTable::class,
                Join::on('this.UF_TASK_ID', 'ref.ID')
            ))
                ->configureJoinType('inner'),
        ];
    }

    /**
     * Returns validators for UF_HISTORYCHANGE field.
     *
     * @return array
     */
    public static function validateUfHistorychange()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_LINK field.
     *
     * @return array
     */
    public static function validateUfLink()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_LINK_PRODUCT field.
     *
     * @return array
     */
    public static function validateUfLinkProduct()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_REPORT_LINK field.
     *
     * @return array
     */
    public static function validateUfReportLink()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_REPORT_SCREEN field.
     *
     * @return array
     */
    public static function validateUfReportScreen()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_REPORT_FILE field.
     *
     * @return array
     */
    public static function validateUfReportFile()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }

    /**
     * Returns validators for UF_REPORT_TEXT field.
     *
     * @return array
     */
    public static function validateUfReportText()
    {
        return [
            new LengthValidator(null, 2048),
        ];
    }
}
