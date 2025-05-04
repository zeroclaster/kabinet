<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class FormContractComponent extends \Bitrix\Kabinet\component\Basecreator implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    /**
     * Base constructor.
     * @param \CBitrixComponent|null $component     Component object if exists.
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
    }

	public function makeId(array $params): string
	{
		return $params["ENTITY_ID"]."_".$params["FIELD_NAME"];
	}

    public function prepareData(){

        // $GLOBALS["USER_FIELD_MANAGER"]
        // /bitrix/modules/main/classes/mysql/usertype.php
		// /bitrix/modules/main/classes/general/usertypemanager.php

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');

        // Физического лица
        // Индивидуального предпринимателя
        // ...
        $usertype = \CUserOptions::GetOption('kabinet','usertype',false,$user->get('ID'));
        if (!$usertype){
            // Физического лица
            $usertype = 1;
            \CUserOptions::SetOption('kabinet','usertype',$usertype,false,$user->get('ID'));
        }

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $contractManager = $sL->get('Kabinet.Contract');
        $bankManager = $sL->get('Kabinet.Bankdata');
        $arResult['DATA'] = $contractManager->getData();
        $arResult['DATA2'] = $bankManager->getData();

        $fields = $this->getUserFields();
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
		foreach($fields as $val) $arResult['DATA_CONTRACT_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

			$id = $this->makeId($params);
			$name = $id;
			$attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields.'.$params["FIELD_NAME"]];
			
            $classF = $params['USER_TYPE']['CLASS_NAME'];

            $arResult["FIELDS"][$id] = $params;
            $arResult["FIELDS"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields','VMODEFIELDSETTINGS'=>'contractsettings']);

            $arResult["FIELDS"][$id]['VUE_FIELD_NAME'] = 'fields';
            $arResult["FIELDS"][$id]['TYPE_VIEW'] = $contractManager->fieldsType[$params["FIELD_NAME"]];
        }


        $fields = $this->getUserFields(BANKDATE);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
		foreach($fields as $val) $arResult['DATA_BANK_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields2.'.$params["FIELD_NAME"]];

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS2"][$id] = $params;
            $arResult["FIELDS2"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields2','VMODEFIELDSETTINGS'=>'banksettings']);

            $arResult["FIELDS2"][$id]['VUE_FIELD_NAME'] = 'fields2';
            $arResult["FIELDS2"][$id]['TYPE_VIEW'] = $bankManager->fieldsType[$params["FIELD_NAME"]];
        }

        $allFields = array_merge($arResult["FIELDS"],$arResult["FIELDS2"]);

        foreach ($arParams['GROUPS'] as $key => $groupdatum) {
            array_walk($arParams['GROUP' .$key], function (&$item, $key, $allFields) {
                    $item = $allFields[$item];
        }, $allFields);
        }

        //\Dbg::print_r($arParams['GROUP1']);

    }
    
    public function doitAction()
    {
        //$name = $this->arParams['QUERY_VARIABLE'];
        //$str = $this->arParams['~'.$name];

        //$context = \Bitrix\Main\Application::getInstance()->getContext();
        //$server = $context->getServer();
        //$request = $context->getRequest();
        //$settings = $request->get('sourcehtml');

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