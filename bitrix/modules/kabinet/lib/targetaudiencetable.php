<?php
namespace Bitrix\Kabinet;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\FloatField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class TargetAudienceTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_PROJECT_ID int optional
 * <li> UF_TARGET_AUDIENCE text optional
 * <li> UF_COUNTRY text optional
 * <li> UF_REGION text optional
 * <li> UF_CITY text optional
 * <li> UF_RATIO_GENDERS double optional
 * </ul>
 *
 * @package Bitrix\Kabinet
 **/

class TargetAudienceTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kabinet_target_audience';
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
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'UF_PROJECT_ID',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_PROJECT_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_TARGET_AUDIENCE',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_TARGET_AUDIENCE_FIELD')
                ]
            ),
            new TextField(
                'UF_COUNTRY',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_COUNTRY_FIELD')
                ]
            ),
            new TextField(
                'UF_REGION',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_REGION_FIELD')
                ]
            ),
            new TextField(
                'UF_CITY',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_CITY_FIELD')
                ]
            ),
            new FloatField(
                'UF_RATIO_GENDERS',
                [
                    'title' => Loc::getMessage('TARGET_AUDIENCE_ENTITY_UF_RATIO_GENDERS_FIELD')
                ]
            ),
        ];
    }
}