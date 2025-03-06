<?php
namespace Bitrix\Kabinet\billing;

use \Bitrix\Main\SystemException;

class History extends \Bitrix\Kabinet\container\Hlbase {

    // поля которые выводятся при выборе в селекте
    // например "UF_NAME"=>[1],
    public $fieldsType = [];

    public function __construct(int $id, $HLBCClass)
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new SystemException("Сritical error! Registered users only.");

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Billinghistory::OnBeforeAdd", [$this,"OnBeforeAdd"]);
        //AddEventHandler("", "\Billinghistory::OnBeforeUpdate", [$this,"OnBeforeUpdate"]);
        //AddEventHandler("", "\Billinghistory::OnBeforeDelete", [$this,"OnBeforeDelete"]);
    }

    public function OnBeforeAdd($fields,$object)
    {
    }

    public function OnBeforeUpdate($id,$fields,$object)
    {
    }

    public function OnBeforeDelete($id)
    {
    }

    public function clearCache(){
    }

    public function addHistory($peration,$initiator,$value){
		global $USER;
		
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $user = (\KContainer::getInstance())->get('siteuser');

        $fields = [];

        if ($initiator instanceof \Bitrix\Kabinet\taskrunner\states\Basestate){
            $runnerFields = $initiator->runnerFields;
            $fields['UF_QUEUE_ID'] = $runnerFields['ID'];
            $task = $initiator->getTask();
            $fields['UF_TASK_ID'] = $task['ID'];
			$peration = $peration. ' '.$task['UF_NAME'];
            $fields['UF_PROJECT_ID'] = $task['UF_PROJECT_ID'];
			$project = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
				'select'=>['*'],
				'filter'=>['ID'=>$task['UF_PROJECT_ID']],
				'limit' => 1,
			])->fetch();
			

			$fields['UF_USER_EDIT'] = $user->printName().' '.'(email:'.$user['EMAIL'].')';
			$fields['UF_PROJECT'] = $project['UF_NAME'];
            $fields['UF_AUTHOR_ID'] = $task['UF_AUTHOR_ID'];
        }

        if ($initiator instanceof \Bitrix\Kabinet\taskrunner\Runnermanager){
            $task = $initiator->taskFileds;
            $fields['UF_TASK_ID'] = $task['ID'];
            //$peration = $peration. ' '.$task['UF_NAME'];
            $fields['UF_PROJECT_ID'] = $task['UF_PROJECT_ID'];
            $project = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
                'select'=>['*'],
                'filter'=>['ID'=>$task['UF_PROJECT_ID']],
                'limit' => 1,
            ])->fetch();


            $fields['UF_USER_EDIT'] = $user->printName().' '.'(email:'.$user['EMAIL'].')';
            $fields['UF_PROJECT'] = $project['UF_NAME'];
            $fields['UF_AUTHOR_ID'] = $task['UF_AUTHOR_ID'];
        }

        if ($initiator instanceof \Bitrix\Kabinet\billing\paysystem\Baseresult){
            $peration = $peration. ' '.$initiator->getDescription();
            $fields['UF_USER_EDIT'] = $user->printName().' '.'(email:'.$user['EMAIL'].')';
            $fields['UF_AUTHOR_ID'] = $user->get('ID');
        }

        if ($initiator instanceof \Bitrix\Kabinet\container\Hlbase && get_class($initiator) == 'Bitrix\Kabinet\billing\Billing'){
            $fields['UF_USER_EDIT'] = $user->printName().' '.'(email:'.$user['EMAIL'].')';
            $fields['UF_AUTHOR_ID'] = $user->get('ID');
        }

        $billing = $sL->get('Kabinet.Billing');
        $userBilling = $billing->getData(true,['UF_AUTHOR_ID'=>$fields['UF_AUTHOR_ID']]);
        $fields = array_merge($fields,[
            'UF_BILLING_ID'=>$userBilling['ID'],
            'UF_OPERATION'=>$peration,
            'UF_VALUE' => $value,
        ]);

        $ID = $this->add($fields);

        $row = $this->getData(['ID'=>$ID]);
        $row = $row[0];

        $row['UF_AUTHOR_ID'] = $fields['UF_AUTHOR_ID'];

        unset($row['ID']);
        \Bitrix\Kabinet\billing\datamanager\BillinghistoryTable::update($ID,['UF_AUTHOR_ID'=>$fields['UF_AUTHOR_ID']]);
    }

    public function getData($filter = [],$offset=0,$limit=5,$clear=false){
        global $CACHE_MANAGER;

        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');
		
		// make filter....
		if (!$filter){
			$filter = ['UF_AUTHOR_ID'=>$user_id];
		}

        $requestURL = $user_id;
        $cacheSalt = md5($requestURL);
        $cacheId = $requestURL."|".SITE_ID."|".$cacheSalt;

        $cache = new \CPHPCache;
        // Clear cache "bank_data"
        if ($clear) $cache->clean($cacheId, "kabinet/billinghistory");
        //$CACHE_MANAGER->ClearByTag("billinghistory_data");

        $cache->clean($cacheId, "kabinet/billinghistory");

        $cache = new \CPHPCache;

        if ($cache->StartDataCache(14400, $cacheId, "kabinet/billinghistory"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("billinghistory_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $dataSQL = \Bitrix\Kabinet\billing\datamanager\BillinghistoryTable::getListActive([
                'select'=>['*'],
                'filter'=>$filter,
                'order'=>["UF_PUBLISH_DATE"=>'DESC'],
				'limit'=>$limit,
				'offset'=>$offset
            ])->fetchAll();

            //echo "<pre>";
            //echo \Bitrix\Main\Entity\Query::getLastQuery();
            //echo "</pre>";
			
			//$dataSQL = array_reverse($dataSQL);

			$listdata = [];
			foreach ($dataSQL as $data) {
                $c = $this->convertData($data, $this->getUserFields());
                $listdata[] = $c;
            }

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
