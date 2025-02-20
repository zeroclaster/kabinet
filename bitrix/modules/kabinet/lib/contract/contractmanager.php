<?php
namespace Bitrix\Kabinet\contract;

use \Bitrix\Main\SystemException;

class Contractmanager extends \Bitrix\Kabinet\container\Hlbase {

    // поля для проверки на обязательное заполненние
    protected $comp_required = [
        "UF_NAME",
        "UF_UR_ADDRESS",
        "UF_INN",
        "UF_KPP",
        "UF_OGRN",
        "UF_MAILN_ADDRESS",
        "UF_FIO",
        "UF_ACTS",
    ];

    protected $fiz_required = [
        "UF_MAILN_ADDRESS",
        "UF_FIO",
        "UF_ACTS",
    ];

    // поля которые выводятся при выборе Физического лица в селекте это 1
	public $fieldsType = [
        "UF_NAME"=>[2,3,4],
        "UF_UR_ADDRESS"=>[2,3,4],
        "UF_INN"=>[2,3,4],
        "UF_KPP"=>[2,3,4],
        "UF_OGRN"=>[2,3,4],
        "UF_MAILN_ADDRESS"=>[1,2,3,4],
        "UF_FIO"=>[1,2,3,4],
        "UF_ACTS"=>[1,2,3,4],
    ];

    public function __construct(int $id, $HLBCClass)
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new SystemException("Сritical error! Registered users only.");

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Contract::OnBeforeAdd", [$this,"OnBeforeAdd"]);
    }

    public function OnBeforeAdd($fields,$object)
    {
    }

    public function getRquired($userType){
        if ($userType == 1) return $this->fiz_required;
        else  return $this->comp_required;
    }

    /*
     * переопределяем свойство только для того что бы принудительно задавать поля свойство обязательные
     * при выборе селекта Физического лица
     */
    public function checkFields($fields,int $HLBLOCK_ID = 0){
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        $post = $request->getPostList()->toArray();

        $required = $this->getRquired($post['contracttype']);

        if (!$HLBLOCK_ID) $HLBLOCK_ID = $this->HB_ID;
        $hl_fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$HLBLOCK_ID,null,LANGUAGE_ID);

        foreach ($hl_fields as $field=>$hfield) {
            if (in_array($hfield['FIELD_NAME'],$required)) $hl_fields[$field]['MANDATORY'] = 'Y';
        }

        $this->requiredField = '';
        $check = [];
        foreach ($hl_fields as $field=>$hfield) {

            if($hfield['MANDATORY'] == 'Y' && empty($fields[$field])){
                $this->requiredField = $hfield["EDIT_FORM_LABEL"];
                break;
            }

            $value = isset($fields[$field])? $fields[$field]: '';

            if (!isset($hfield["SETTINGS"]["REGEXP"]) || $hfield["SETTINGS"]["REGEXP"] == '') continue;
            if (!preg_match($hfield["SETTINGS"]["REGEXP"], $value, $matches) && $matches[0] == $value) {
                $this->requiredField = $hfield["EDIT_FORM_LABEL"];
                break;
            }
        }

        if ($this->requiredField) return false;
        return true;
    }

    public function getData($clear=false){
        global $CACHE_MANAGER;

        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        $requestURL = $user_id;
        $cacheSalt = md5($requestURL);
        $cacheId = $requestURL."|".SITE_ID."|".$cacheSalt;

        $cache = new \CPHPCache;
        // Clear cache "project_data"
        if ($clear) $cache->clean($cacheId, "kabinet/contract");
        //$CACHE_MANAGER->ClearByTag("contract_data");

        $cache->clean($cacheId, "kabinet/contract");

        $cache = new \CPHPCache;

        if ($cache->StartDataCache(14400, $cacheId, "kabinet/contract"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("contract_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $data = \Bitrix\Kabinet\contract\datamanager\ContractTable::getlist([
                'select'=>['*'],
                'filter'=>['UF_AUTHOR_ID'=>$user_id],
                'order'=>["UF_PUBLISH_DATE"=>'DESC'],
                'limit'=>1
            ])->fetch();


            $listdata = $this->convertData($data, $this->getUserFields());

            if (defined("BX_COMP_MANAGED_CACHE")) $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache(array($listdata));
        }
        else
        {
            $vars = $cache->GetVars();
            $listdata = $vars[0];
        }

        return $listdata;
    }
}
