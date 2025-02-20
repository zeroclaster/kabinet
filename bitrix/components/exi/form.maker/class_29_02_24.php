<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class FormMakerComponent extends \Bitrix\Kabinet\component\Basecreator implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    /**
     * Base constructor.
     * @param \CBitrixComponent|null $component     Component object if exists.
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
    }

    public function onPrepareComponentParams($params)
    {
        $request =$this->request;

        $name = $params['QUERY_VARIABLE'];
        if ($request->getQuery($name))
            $params[$name] = $request->getQuery($name);
        else
            $params[$name] = $request->get($name);

        return $params;
    }

    public function prepareData(){

        // $GLOBALS["USER_FIELD_MANAGER"]
        // /bitrix/modules/main/classes/mysql/usertype.php
		// /bitrix/modules/main/classes/general/usertypemanager.php

        $arResult = &$this->arResult;
        $arParams = $this->arParams;

        $fields = $this->getUserFields();
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        if ($arParams["ID"])
            $arResult["ACTION"] = 'EDIT';
        else
            $arResult["ACTION"] = 'NEW';

        if ($arParams['FIELDS'] == 'ALL') $arResult["FIELDS"] =  $fields;

		
        foreach($fields as $name => $params){
			
			$id = $params["ENTITY_ID"]."_".$params["FIELD_NAME"];
			$name = $params["ENTITY_ID"]."_".$params["FIELD_NAME"];
			$attr = ['id'=>$id,'class' => "form-control"];
			
            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS"][$name]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr]);
        }


        $makedFields = \Bitrix\Kabinet\ProjectfieldsTable::getListActive()->fetchAll();
        $allAdditionals = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_5',null,LANGUAGE_ID);
		
		
		//echo "<pre>";
		//print_r($makedFields);
		///echo "</pre>";

        $arResult['additionals'] = [];

        foreach ($makedFields as $maked) {
            if ($maked['UF_TYPE'] == 'string')
                $mutated = $allAdditionals['UF_VALUE_STR'];
			
            if ($maked['UF_TYPE'] == 'multistring')
                $mutated = $allAdditionals['UF_VALUE_MULTI'];			
			
			if ($maked['UF_TYPE'] == 'hblist')
                $mutated = $allAdditionals['UF_TOPICS'];
			
			if ($maked['UF_MULTIPLE']) $mutated['MULTIPLE'] = 'Y';

            $mutated['FIELD_NAME_ORIGINAL'] = $mutated['FIELD_NAME'];
			$mutated['FIELD_NAME'] = $maked['UF_CODE'];
            $mutated['EDIT_FORM_LABEL'] = $maked['UF_NAME'];
            
			// Подменяем сеттингс, если нужно что-то изменить
			if($UF_SETTINGS = unserialize($maked['UF_SETTINGS']))
					$mutated['SETTINGS'] = $UF_SETTINGS;

            $id = $mutated["ENTITY_ID"]."_".$maked['UF_CODE'];
            $name = $mutated["ENTITY_ID"]."_".$maked['UF_CODE'];
            $attr = ['id'=>$id,'class' => "form-control"];

			$arResult['additionals'][$name] = $mutated;

			// Создаем HTMP для вывода в форму
            $classF = $mutated['USER_TYPE']['CLASS_NAME'];            
            $arResult['additionals'][$name]['PUBLIC_EDIT'] = $classF::getPublicEdit($mutated,['NAME'=>$name,"attribute"=>$attr]);
        }
    }
    
    public function doitAction()
    {
        $name = $this->arParams['QUERY_VARIABLE'];
        $str = $this->arParams['~'.$name];

        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $server = $context->getServer();
        $request = $context->getRequest();
        $settings = $request->get('sourcehtml');

        $output = "";

        //$this->errorCollection[] = new Error('You are so beautiful or so handsome');
        if ($this->hasErrors())
        return null;

  

        /*
        $errors = $result->getErrorMessages();
        $this->errorCollection[] = new Error($errors);
        return null;
        */

        return $output;
    }

    /* signed params*/
    protected function listKeysSignedParameters()
    {
        return [
            'QUERYVARIABLE'
        ];
    }

    public function configureActions()
    {
        //если действия не нужно конфигурировать, то пишем просто так. И будет конфиг по умолчанию
        return [
            'doit' => [
                'prefilters' => [
                    //new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ]
            ]

        ];
    }
}