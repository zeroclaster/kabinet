<?php
namespace Bitrix\Kabinet\billing;

use \Bitrix\Main\SystemException;

class Billing extends \Bitrix\Kabinet\container\Hlbase {
    // поля которые выводятся при выборе в селекте
    // например "UF_NAME"=>[1],
    public $fieldsType = [];


    public function __construct(int $id, $HLBCClass,$config=[])
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new SystemException("Сritical error! Registered users only.");

        $this->config = $config;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $sL->addInstanceLazy("Kabinet.BilligHistory", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\billing\Providerhistory::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Billing::OnBeforeAdd", [$this,"OnBeforeAddHandler"]);
        AddEventHandler("", "\Billing::OnBeforeUpdate", [$this,"OnBeforeUpdateHandler"]);
        AddEventHandler("", "\Billing::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);

        AddEventHandler("", "\Bitrix\Kabinet\billing\datamanager\TransactionTable::OnBeforeAdd", [$this,"valiedateHandler"]);
    }

    public function valiedateHandler($fields,$object)
    {
        if(empty($fields['BILING_ID']) ||
        empty($fields['USER_ID']) ||
        empty($fields['SUM']))
            throw new SystemException("error");
    }

    public function OnBeforeAddHandler($fields,$object)
    {
    }

    public function OnBeforeUpdateHandler($id,$primary,$fields,$object,$oldData)
    {
		//if($fields['UF_VALUE'] < 0) throw new SystemException("Значение биллинга не может быть отрицательным числом");
    }

    public function OnBeforeDeleteHandler($id)
    {
    }

    public function clearCache(){
    }

    public function cachback($value,$user_id = 0,$initiator){
        if ($user_id)
            $filter = ['UF_AUTHOR_ID'=>$user_id];
        else
            $filter = [];
        $userMoney = $this->getData(true,$filter);

        $calc = $userMoney['UF_VALUE'] + $value;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $history = $sL->get('Kabinet.BilligHistory');

        $this->update(['ID'=>$userMoney['ID'],'UF_VALUE'=>$calc]);
        $history->addHistory('Возврат средств. Отмена исполнения по задаче: ',$initiator,$value);
    }

    public function cachback2($value,$user_id = 0,$initiator){
        if ($user_id)
            $filter = ['UF_AUTHOR_ID'=>$user_id];
        else
            $filter = [];
        $userMoney = $this->getData(true,$filter);

        $calc = $userMoney['UF_VALUE'] + $value;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $history = $sL->get('Kabinet.BilligHistory');

        $this->update(['ID'=>$userMoney['ID'],'UF_VALUE'=>$calc]);
        $history->addHistory('Возврат средств. По задаче: ',$initiator,$value);
    }

    public function addMoney($value,$user_id = 0,$initiator){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        if ($user_id)
            $filter = ['UF_AUTHOR_ID'=>$user_id];
        else
            $filter = [];

        $billing = $this->getData(true,$filter);
        if (!$billing) throw new SystemException("Не удалось определить биллинг для пользователя"." ". " id:".$user_id);

        $Money = floatval($billing['UF_VALUE']);

        $calc = $Money + $value;
        $this->update(['ID'=>$billing['ID'],'UF_VALUE'=>$calc]);
        $this->getData($clear=true,$filter);
        $history = $sL->get('Kabinet.BilligHistory');
        $history->addHistory('Пополнение баланса.',$initiator,$value);
    }


    public function teorygetMoney($value,$user_id = 0){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        if ($user_id)
            $filter = ['UF_AUTHOR_ID'=>$user_id];
        else
            $filter = [];

        $userMoney = $this->getData(true,$filter);
        if (!$userMoney) throw new SystemException("Не удалось определить биллинг для пользователя"." ". " id:".$user_id);

        $Money = floatval($userMoney['UF_VALUE']);

        if ($value > $Money) return false;

        return true;
    }

    public function getMoney($value,$user_id = 0,$initiator){
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		if ($user_id)
            $filter = ['UF_AUTHOR_ID'=>$user_id];
        else
            $filter = [];
		
        $userMoney = $this->getData(true,$filter);
		if (!$userMoney) throw new SystemException("Не удалось определить биллинг для пользователя"." ". " id:".$user_id);

		$Money = floatval($userMoney['UF_VALUE']);

        if ($value > $Money) return false;

        $calc = $Money - $value;
        // Bug!????
        //$calc = $calc * -1;

        try {
            $this->update(['ID' => $userMoney['ID'], 'UF_VALUE' => $calc]);
        }
        catch (\Bitrix\Main\SystemException $exception){
                //var_dump($exception->getMessage());
                return null;
        }

		$history = $sL->get('Kabinet.BilligHistory');
		$history->addHistory('Списание по задаче: ',$initiator,$value);

        return $value;
    }

    public function getData($clear=false,$filter = []){
        global $CACHE_MANAGER;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();

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
        // Clear cache "billing_data"
        if ($clear) $cache->clean($cacheId, "kabinet/billingdata");
        //$CACHE_MANAGER->ClearByTag("billing_data");

        $cache->clean($cacheId, "kabinet/billingdata");

        $cache = new \CPHPCache;

        if ($cache->StartDataCache(14400, $cacheId, "kabinet/billingdata"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("billing_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $billingSQL = datamanager\BillingTable::getListActive([
                'select'=>['*'],
                'filter'=>$filter,
				'limit'=>1
            ])->fetch();

            //echo "<pre>";
            //echo \Bitrix\Main\Entity\Query::getLastQuery();
            //echo "</pre>";

            // если у пользователя еще не создан биллинг, создаем его
            if (!$billingSQL && isset($filter['UF_AUTHOR_ID'])){
                if ($user_id>0) {
                    $upd_id = $this->add([
                        'UF_AUTHOR_ID' => $user_id,
                        'UF_VALUE' => $this->config('START_BILLING'),
                    ]);

                    $history = $sL->get('Kabinet.BilligHistory');
                    $initiator = $this;
                    $history->addHistory('Зачислено на баланс ' . $this->config('START_BILLING') . ' рублей.', $initiator, $this->config('START_BILLING'));

                    $billingSQL = datamanager\BillingTable::getListActive([
                        'select' => ['*'],
                        'filter' => ['ID' => $upd_id],
                        'limit' => 1
                    ])->fetch();
                }
                else return [];
            }
       
            $listdata = $this->convertData($billingSQL, $this->getUserFields());


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

    protected function findQeue(array $filter){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $taskManager = $sL->get('Kabinet.Task');

        $l = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
            'select' => ['ID','UF_TASK_ID','UF_MONEY_RESERVE','UF_ELEMENT_TYPE','TASK.*'],
            'filter'=>$filter,
            'runtime' => [
                'TASK' => [
                    'data_type' => \Bitrix\Kabinet\task\datamanager\TaskTable::class,
                    'reference' => ['=this.UF_TASK_ID' => 'ref.ID',],
                    'join_type' => 'INNER'
                ],
            ]
        ])->fetchAll();

        //echo "<pre>";
       //echo \Bitrix\Main\Entity\Query::getLastQuery();
        //echo "</pre>";

        $epn = [];
        foreach ($l as $item){
            $task = [];
            foreach ($item as $fieldName => $value) {
                if (strpos($fieldName,'KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_') !== false){
                    $n = str_replace('KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_','',$fieldName);
                    $task[$n] = $value;
                }
            }

            $PRODUCT = $taskManager->getProductByTask($task);

            // если исполнение Множественное (ELEMENT_TYPE => multiple), то стоимость это стоимость из каталога * кол.
            if ($item['UF_ELEMENT_TYPE'] == 'multiple')
                $epn[] = ['TASK_ID'=>$task['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1']*$task['UF_NUMBER_STARTS']];
            else
                $epn[] = ['TASK_ID'=>$task['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1']];
        }

        return $epn;
    }

    public function lastMonthExpenses($project_id=0){
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        // Начало следующего месяца
        // Конец следующего месяца
        [$mouthStart,$mouthEnd] = \PHelp::lastMonth();

        $filter=[
            'UF_ACTIVE'=>1,
            '>UF_PLANNE_DATE'=>$mouthStart,
            '<UF_PLANNE_DATE'=>$mouthEnd,
            //'UF_STATUS'=>0,
            'TASK.UF_ACTIVE'=>1,
            'TASK.UF_AUTHOR_ID'=>$user_id,
        ];

        if ($project_id) $filter['TASK.UF_PROJECT_ID'] = $project_id;
        $epn = $this->findQeue($filter);
        $sum = 0;
        foreach ($epn as $item) $sum += $item['VALUE'];

        return $sum;
    }

    public function actualMonthExpenses($project_id=0){
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        // Начало следующего месяца
        // Конец следующего месяца
        [$mouthStart,$mouthEnd] = \PHelp::actualMonth();

        $filter=[
            'UF_ACTIVE'=>1,
            '>UF_PLANNE_DATE'=>$mouthStart,
            '<UF_PLANNE_DATE'=>$mouthEnd,
            //'UF_STATUS'=>0,
            'UF_STATUS'=>9, // Выполненые
            'TASK.UF_ACTIVE'=>1,
            'TASK.UF_AUTHOR_ID'=>$user_id,
        ];

        if ($project_id) $filter['TASK.UF_PROJECT_ID'] = $project_id;
        $epn = $this->findQeue($filter);
        $sum = 0;
        foreach ($epn as $item) $sum += $item['VALUE'];

        return $sum;
    }

    public function actualMonthBudget($project_id=0){
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        // Начало следующего месяца
        // Конец следующего месяца
        [$mouthStart,$mouthEnd] = \PHelp::actualMonth();

        $filter=[
            'UF_ACTIVE'=>1,
            '>UF_PLANNE_DATE'=>$mouthStart,
            '<UF_PLANNE_DATE'=>$mouthEnd,
            //'UF_STATUS'=>0,
            'TASK.UF_ACTIVE'=>1,
            'TASK.UF_AUTHOR_ID'=>$user_id,
        ];

        if ($project_id) $filter['TASK.UF_PROJECT_ID'] = $project_id;
        $epn = $this->findQeue($filter);
        $sum = 0;
        foreach ($epn as $item) $sum += $item['VALUE'];

        return $sum;
    }

    public function nextMonthExpenses($project_id=0){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $taskManager = $sL->get('Kabinet.Task');
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        // Начало следующего месяца
        // Конец следующего месяца
        [$mouthStart,$mouthEnd] = \PHelp::nextMonth();

        $filter=[
            'UF_ACTIVE'=>1,
            '>UF_PLANNE_DATE'=>$mouthStart,
            '<UF_PLANNE_DATE'=>$mouthEnd,
            'UF_STATUS'=>0,
            'TASK.UF_ACTIVE'=>1,
            'TASK.UF_AUTHOR_ID'=>$user_id,
        ];

        if ($project_id) $filter['TASK.UF_PROJECT_ID'] = $project_id;

        $epn = $this->findQeue($filter);

        $filter2 = [
            'UF_ACTIVE'=>1,
            'UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED,
            'UF_AUTHOR_ID'=>$user_id,
            'UF_CYCLICALITY'=> [2,34],
        ];
        if ($project_id) $filter2['UF_PROJECT_ID'] = $project_id;

        $t = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist(['select'=>['*'], 'filter'=>$filter2])->fetchAll();

        $future = [];
        foreach ($t as $item){
            $key = array_search($item['ID'], array_column($epn, 'TASK_ID'));
            if ($key !== false) continue;

            $PRODUCT = $taskManager->getProductByTask($item);
            if ($PRODUCT['ELEMENT_TYPE']['VALUE'] == 'multiple')
                $future[] = ['TASK_ID'=>$item['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1']*$item['UF_NUMBER_STARTS']];
            else
                $future[] = ['TASK_ID'=>$item['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1']];
        }

        $sum = 0;
        foreach ($epn as $item) $sum += $item['VALUE'];
        foreach ($future as $item) $sum += $item['VALUE'];

        return $sum;
    }

    public function findDate($a,$epn_ = [],$date_,$date_2,$ret){

        //\Dbg::print_r([$date_,$date_2]);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billing = $sL->get('Kabinet.Billing');
        $taskManager = $sL->get('Kabinet.Task');

        // Начало следующего месяца
        $mouthStart = new \Bitrix\Main\Type\DateTime(
            $date_->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        );

        // Конец следующего месяца
        $mouthEnd = (new \Bitrix\Main\Type\DateTime(
            $date_2->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        ));

        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        // ищем уже запланированные на в этом месяце
        $l = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
            'select' => ['ID','UF_TASK_ID','UF_PLANNE_DATE','UF_MONEY_RESERVE','TASK.*'],
            'filter'=>[
                'UF_ACTIVE'=>1,
                '>UF_PLANNE_DATE'=>$mouthStart,
                '<=UF_PLANNE_DATE'=>$mouthEnd,
                'UF_STATUS'=>0,
                'TASK.UF_ACTIVE'=>1,
                'TASK.UF_AUTHOR_ID'=>$user_id,
            ],
            'runtime' => [
                'TASK' => [
                    'data_type' => \Bitrix\Kabinet\task\datamanager\TaskTable::class,
                    'reference' => ['=this.UF_TASK_ID' => 'ref.ID',],
                    'join_type' => 'INNER'
                ],
            ]
        ])->fetchAll();

        //echo \Bitrix\Main\Entity\Query::getLastQuery();
        $epn = [];
        foreach ($l as $item){
            $task = [];
            foreach ($item as $fieldName => $value) {
                if (strpos($fieldName,'KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_') !== false){
                    $n = str_replace('KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_','',$fieldName);
                    $task[$n] = $value;
                }
            }

            $PRODUCT = $taskManager->getProductByTask($task);
            if ($item['UF_ELEMENT_TYPE'] == 'multiple')
                $epn[] = ['TASK_ID'=>$task['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1']*$task['UF_NUMBER_STARTS'],'UF_PLANNE_DATE'=>$item['UF_PLANNE_DATE']];
            else
                $epn[] = ['TASK_ID'=>$task['ID'],'VALUE'=>$PRODUCT['CATALOG_PRICE_1'],'UF_PLANNE_DATE'=>$item['UF_PLANNE_DATE']];
        }

        $t = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
            'select'=>['*'],
            'filter'=>[
                'UF_ACTIVE'=>1,
                'UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED,
                'UF_AUTHOR_ID'=>$user_id,
                'UF_CYCLICALITY'=> [2,34],
            ],
        ])->fetchAll();

        $future = [];
        foreach ($t as $item){
            $key = array_search($item['ID'], array_column($epn, 'TASK_ID'));
            if ($key !== false) continue;

            $PRODUCT = $taskManager->getProductByTask($item);
            $step = floor(30 / $item['UF_NUMBER_STARTS']);
            for($i=0;$i<$item['UF_NUMBER_STARTS'];$i++) {

                $calcDaysStep = $step * ($i+1);
                // Начало следующего месяца
                $mouthStart = new \Bitrix\Main\Type\DateTime(
                    $date_->format("d.m.Y 00:00:01"),
                    "d.m.Y H:i:s"
                );
                if ($PRODUCT['ELEMENT_TYPE']['VALUE'] == 'multiple')
                    $future[] = [
                        'TASK_ID'=>$task['ID'],
                        'VALUE'=>$PRODUCT['CATALOG_PRICE_1']*$item['UF_NUMBER_STARTS'],
                        'UF_PLANNE_DATE'=>$mouthStart->add("+".$calcDaysStep.' days')
                    ];
                else
                    $future[] = [
                        'TASK_ID'=>$task['ID'],
                        'VALUE'=>$PRODUCT['CATALOG_PRICE_1'],
                        'UF_PLANNE_DATE'=>$mouthStart->add("+".$calcDaysStep.' days')
                    ];
            }
        }


        $epn = array_merge($epn,$future,$epn_);
        $sum = 0;
        $BILLING = $billing->getData();
        foreach (array_column($epn,'VALUE') as $index => $v){
            $sum += $v;
            if ($sum>$BILLING['UF_VALUE']){
                $arr_rev = array_reverse($epn);
                $ret = $arr_rev[$index]['UF_PLANNE_DATE'];
                break;
            }
        }

        // $a - защита от зависания
        if ($a < 100 && $sum<$BILLING['UF_VALUE']) {
            $a = $a + 1;

            // сдвигаемся на месяц вперед
            $date_ = $date_->modify( 'first day of next month' );
            $date_2 = $date_2->modify( 'last day of next month' );
            [$a,$epn_,$date_,$date_2,$ret] = $this->findDate($a,$epn,$date_,$date_2,$ret);
        }


        return [$a,$epn_,$date_,$date_2,$ret];
    }

    public function createTransaction($sum){
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        $billing = datamanager\BillingTable::getListActive([
            'select'=>['ID'],
            'filter'=>['UF_AUTHOR_ID'=>$user_id],
            'limit'=>1
        ])->fetch();

        $obResult = \Bitrix\Kabinet\billing\datamanager\TransactionTable::add([
            'SUM'=>$sum,
            'DATE_OPERATION'=> new \Bitrix\Main\Type\DateTime(),
            'USER_ID' => $user_id,
            'BILING_ID' => $billing['ID'],

        ]);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }

        return $obResult->getID();
    }
}
