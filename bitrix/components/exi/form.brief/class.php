<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class FormBriefComponent extends \Bitrix\Kabinet\component\Basecreator implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

        $user = (\KContainer::getInstance())->get('user');

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');
        $infoManager = $sL->get('Kabinet.infoProject');
        $detailsManager = $sL->get('Kabinet.detailsProject');
        $targetManager = $sL->get('Kabinet.targetProject');
        $arResult['DATA_PROJECT'] = [];
        $arResult['DATA_INFOPROJECT'] = [];
        $arResult['DATA_DETAILSPROJECT'] = [];
        $arResult['DATA_TARGETPROJECT'] = [];

        if($arParams['ID']) {
            $data = $projectManager->getData();
            $key = array_search($arParams['ID'], array_column($data, 'ID'));
            $arResult['DATA_PROJECT'] = $data[$key];
            $project_id = $arResult['DATA_PROJECT']['ID'];

            $data2 = $infoManager->getData($project_id);
            $arResult['DATA_INFOPROJECT'] = $data2[$project_id];


            $data3 = $detailsManager->getData($project_id);
            $arResult['DATA_DETAILSPROJECT'] = $data3[$project_id];

            $data4 = $targetManager->getData($project_id);
            $arResult['DATA_TARGETPROJECT'] = $data4[$project_id];
        }else{
            $arResult['DATA_PROJECT'] = $projectManager->getEmptyData();
            $arResult['DATA_INFOPROJECT'] = $infoManager->getEmptyData();
            $arResult['DATA_DETAILSPROJECT'] = $detailsManager->getEmptyData();
            $arResult['DATA_TARGETPROJECT'] = $targetManager->getEmptyData();
        }

        $fields = $this->getUserFields();
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $val) $arResult['DATA_PROJECT_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

			$id = $this->makeId($params);
			$name = $id;
			$attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields.'.$params["FIELD_NAME"]];
			
            $classF = $params['USER_TYPE']['CLASS_NAME'];

            $arResult["FIELDS"][$id] = $params;
            $arResult["FIELDS"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields','VMODEFIELDSETTINGS'=>'projectsettings']);

            $arResult["FIELDS"][$id]['VUE_FIELD_NAME'] = 'fields';
            $arResult["FIELDS"][$id]['TYPE_VIEW'] = $projectManager->fieldsType[$params["FIELD_NAME"]];
        }


        $fields = $this->getUserFields(PROJECTSINFO);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $val) $arResult['DATA_INFOPROJECT_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){
            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields2.'.$params["FIELD_NAME"]];

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS2"][$id] = $params;
            $arResult["FIELDS2"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields2','VMODEFIELDSETTINGS'=>'infosettings']);

            $arResult["FIELDS2"][$id]['VUE_FIELD_NAME'] = 'fields2';
            $arResult["FIELDS2"][$id]['TYPE_VIEW'] = $infoManager->fieldsType[$params["FIELD_NAME"]];
        }

        $fields = $this->getUserFields(PROJECTSDETAILS);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $val) $arResult['DATA_DETAILSPROJECT_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){

            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields3.'.$params["FIELD_NAME"]];

            if($params["USER_TYPE"]["USER_TYPE_ID"] == "richtext") $attr['original'] = 'fields3.'.$params["FIELD_NAME"].'_ORIGINAL';

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS3"][$id] = $params;
            $arResult["FIELDS3"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields3','VMODEFIELDSETTINGS'=>'detailssettings']);

            $arResult["FIELDS3"][$id]['VUE_FIELD_NAME'] = 'fields3';
            $arResult["FIELDS3"][$id]['TYPE_VIEW'] = $infoManager->fieldsType[$params["FIELD_NAME"]];
        }


        $fields = $this->getUserFields(TARGETAUDIENCE);
        // Убираем все системные поля, их править пользователю не требуется
        $fields = $this->extrudeSysFifelds($fields);
        foreach($fields as $val) $arResult['DATA_TARGETPROJECT_SETTINGS'][$val['FIELD_NAME']] = $val['SETTINGS'];
        foreach($fields as $key => $params){

            if(preg_match('/_USER$/', $params["FIELD_NAME"], $output_array)) continue;

            $id = $this->makeId($params);
            $name = $id;
            $attr = ['id'=>$id,'class' => "form-control",'v-model'=>'fields4.'.$params["FIELD_NAME"]];

            if($params["USER_TYPE"]["USER_TYPE_ID"] == "richtext") $attr['original'] = 'fields4.'.$params["FIELD_NAME"].'_ORIGINAL';

            $classF = $params['USER_TYPE']['CLASS_NAME'];
            $arResult["FIELDS4"][$id] = $params;
            $arResult["FIELDS4"][$id]['PUBLIC_EDIT'] = $classF::getPublicEdit($params,['mediaType_'=>'brief','NAME'=>$name,"attribute"=>$attr,'VMODEFIELD'=>'fields4','VMODEFIELDSETTINGS'=>'targetsettings']);

            $arResult["FIELDS4"][$id]['VUE_FIELD_NAME'] = 'fields4';
            $arResult["FIELDS4"][$id]['TYPE_VIEW'] = $infoManager->fieldsType[$params["FIELD_NAME"]];
        }

        $allFields = array_merge($arResult["FIELDS"],$arResult["FIELDS2"],$arResult["FIELDS3"],$arResult["FIELDS4"]);

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