<?php
namespace Bitrix\Kabinet\contract;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\BankException,
    \Bitrix\Kabinet\exceptions\TestException;

class Bankdatamanager extends \Bitrix\Kabinet\container\Hlbase {

    // поля которые выводятся при выборе в селекте
    // например "UF_NAME"=>[1],
    public $fieldsType = [];

    public function __construct(int $id, $HLBCClass)
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new BankException("Сritical error! Registered users only.");

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Contract::OnBeforeAdd", [$this,"OnBeforeAdd"]);
    }

    public function OnBeforeAdd($fields,$object)
    {
    }

    public function getData($clear=false){
        global $CACHE_MANAGER;

        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        $requestURL = $user_id;
        $cacheSalt = md5($requestURL);
        $cacheId = $requestURL."|".SITE_ID."|".$cacheSalt;

        $cache = new \CPHPCache;
        // Clear cache "bank_data"
        if ($clear) $cache->clean($cacheId, "kabinet/bankdata");
        //$CACHE_MANAGER->ClearByTag("bank_data");

        $cache->clean($cacheId, "kabinet/bankdata");

        $cache = new \CPHPCache;

        if ($cache->StartDataCache(14400, $cacheId, "kabinet/bankdata"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("bank_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $data = \Bitrix\Kabinet\contract\datamanager\PaymentdataTable::getlist([
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
