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
	
	public function makeId(array $params): string
	{
		return $params["ENTITY_ID"]."_".$params["FIELD_NAME"];
	}

    public function prepareData(){

        // $GLOBALS["USER_FIELD_MANAGER"]
        // /bitrix/modules/main/classes/mysql/usertype.php
		// /bitrix/modules/main/classes/general/usertypemanager.php

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $fields = $this->getUserFields();
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
		$data = [];
        if ($arParams["ID"]){
            $arResult["ACTION"] = 'EDIT';		
			$HLBClass = (\KContainer::getInstance())->get(BRIEF_HL);
			$HLBClass2 = (\KContainer::getInstance())->get(PROJECTSINFO_HL);
			$HLBClass3 = (\KContainer::getInstance())->get(PROJECTSDETAILS_HL);
						
			$data = $HLBClass::getlist(['filter'=>['ID'=>$arParams["ID"]],'limit'=>1])->fetch();			
			$data2 = $HLBClass2::getlist(['filter'=>['UF_PROJECT_ID'=>$arParams["ID"]],'limit'=>1])->fetch();
			$data3 = $HLBClass3::getlist(['filter'=>['UF_PROJECT_ID'=>$arParams["ID"]],'limit'=>1])->fetch();
        }else
            $arResult["ACTION"] = 'NEW';

        //if ($arParams['FIELDS'] == 'ALL') $arResult["FIELDS"] =  $fields;
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

			if ($data)
				$params["VALUE"] = $data[$params["FIELD_NAME"]];

            $arResult["FIELDS"][$key] = $params;
			$id = $this->makeId($params);
			$name = $id;
			$attr = ['id'=>$id,'class' => "form-control"];
			
            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS"][$key]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr]);
        }

        $fields = $this->getUserFields(PROJECTSINFO);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

			if ($data2)
				$params["VALUE"] = $data2[$params["FIELD_NAME"]];

            $arResult["FIELDS2"][$key] = $params;
            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control"];

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS2"][$key]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr]);
        }

        $fields = $this->getUserFields(PROJECTSDETAILS);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

			if ($data3)
				$params["VALUE"] = $data3[$params["FIELD_NAME"]];

            $arResult["FIELDS3"][$key] = $params;
            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control"];

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS3"][$key]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr]);
        }

        $fields = $this->getUserFields(TARGETAUDIENCE);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

            $arResult["FIELDS4"][$key] = $params;
            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control"];

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS4"][$key]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr]);
        }

        $allFields = array_merge($arResult["FIELDS"],$arResult["FIELDS2"],$arResult["FIELDS3"],$arResult["FIELDS4"]);

        foreach ($arParams['GROUPS'] as $key => $groupdatum) {
            array_walk($arParams['GROUP' .$key], function (&$item, $key, $allFields) {
                $key = array_search($item, array_column($allFields, 'FIELD_NAME'));
                if ($key !== false)
                    $item = $allFields[$key];
                else
                    $item = [];
        }, $allFields);
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