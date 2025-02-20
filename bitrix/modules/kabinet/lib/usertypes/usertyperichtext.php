<?php
use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeString
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeRichText extends TypeBase
{
    const USER_TYPE_ID = Richtype::USER_TYPE_ID;

    public static function getUserTypeDescription()
    {
        return Richtype::getUserTypeDescription();
    }

    public static function getPublicView($userField, $additionalParameters = array())
    {
        return Richtype::renderView($userField, $additionalParameters);
    }

    public static function getPublicEdit($userField, $additionalParameters = array())
    {
        return Richtype::renderEdit($userField, $additionalParameters);
    }

    function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
    {
        return Richtype::renderSettings($userField, $additionalParameters, $varsFromForm);
    }

    function getEditFormHtml($userField, $additionalParameters)
    {
        return Richtype::renderEditForm($userField, $additionalParameters);
    }

    function getAdminListViewHtml($userField, $additionalParameters)
    {
        return Richtype::renderAdminListView($userField, $additionalParameters);
    }

    function getAdminListEditHtml($userField, $additionalParameters)
    {
        return Richtype::renderAdminListEdit($userField, $additionalParameters);
    }

    function getFilterHtml($userField, $additionalParameters)
    {
        return Richtype::renderFilter($userField, $additionalParameters);
    }

    public static function getDbColumnType()
    {
        return Richtype::getDbColumnType();
    }

    function getFilterData($userField, $additionalParameters)
    {
        return Richtype::getFilterData($userField, $additionalParameters);
    }

    function prepareSettings($userField)
    {
        return Richtype::prepareSettings($userField);
    }

    function checkFields($userField, $value)
    {
        return Richtype::checkFields($userField, $value);
    }

    function onSearchIndex($userField)
    {
        return Richtype::onSearchIndex($userField);
    }
}