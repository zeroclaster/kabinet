<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__); 

try
{
	if (!Main\Loader::includeModule('kabinet'))
		throw new Main\LoaderException(Loc::getMessage('PUBLICATIONS_LIST_MODULE_NOT_INSTALLED'));
	
	$arComponentParameters = array(
    'GROUPS' => array(
    ),
    'PARAMETERS' => array(
      'HB_ID' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_BLOCK_ID'),
        'TYPE' => 'TEXT'
      ),
      'DETAIL_URL' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_DETAIL_URL'),
        'TYPE' => 'TEXT'
      ),
      'COUNT' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_ROWS_PER_PAGE'),
        'TYPE' => 'TEXT'
      ),
      'PAGEN_ID' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_PAGEN_ID'),
        'TYPE' => 'TEXT',
        'DEFAULT' => 'page'
      ),
      'FILTER_NAME' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_FILTER_NAME'),
        'TYPE' => 'TEXT'
      ),
      'SORT_FIELD' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_SORT_FIELD'),
        'TYPE' => 'TEXT',
        'DEFAULT' => 'ID'
      ),
      'SORT_ORDER' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_SORT_ORDER'),
        'TYPE' => 'LIST',
        'DEFAULT' => 'DESC',
        'VALUES' => array(
          'DESC' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_SORT_ORDER_DESC'),
          'ASC' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_SORT_ORDER_ASC')
        )
      ),
      'CHECK_PERMISSIONS' => array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('PUBLICATIONS_LIST_PARAMETERS_CHECK_PERMISSIONS'),
        'TYPE' => 'CHECKBOX'
      ),
    ),
  );
}
catch (Main\LoaderException $e)
{
	ShowError($e->getMessage());
}
?>