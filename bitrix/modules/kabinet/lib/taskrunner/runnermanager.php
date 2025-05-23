<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

namespace Bitrix\Kabinet\taskrunner;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\Exceptions\FulfiException,
    \Bitrix\Kabinet\Exceptions\TestException,
    \Bitrix\Main\Type\Datetime;

class Runnermanager extends \Bitrix\Kabinet\container\Abstracthighloadmanager{
    public $taskFileds = [];
    protected $user;

    public function __construct($user, $HLBCClass,$config=[])
    {
		$this->config = $config;
        $this->user = $user;

        $this->selectFields = $config['ALLOW_FIELDS'];

        parent::__construct($HLBCClass);

        AddEventHandler("", "\Fulfillment::OnBeforeAdd", [$this,'ifCreateNew'],100);
        AddEventHandler("", "\Fulfillment::OnBeforeAdd", [$this,"AutoIncrementAddHandler"]);
        AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'ifChangeStatus'],100);
		AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'CommentifChange'],150);
        AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'historyChangeStatus'],200);
        AddEventHandler("", "\Fulfillment::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);

        AddEventHandler("", "\Task::OnAfterDelete", [$this,"OnBeforeDeleteTaskHandler"]);
    }

    public function OnBeforeDeleteTaskHandler($id, $primary, $oldFields)
    {
        $filter['UF_TASK_ID'] = $id;
        $listdata = $this->HLBCClass::getlist(['select' => ['*'], 'filter'=>$filter])->fetchAll();
        foreach($listdata as $item){
            $this->delete($item['ID']);
        }
    }

    public function AutoIncrementAddHandler($fields,$object)
    {
        $last = $this->HLBCClass::getlist([
            'select'=>['UF_EXT_KEY'],
            'order'=>['ID'=>"DESC"],
            'limit' =>1
        ])->fetch();

        $UF_EXT_KEY = 3000000;
        if ($last && $last['UF_EXT_KEY']>0) $UF_EXT_KEY = $last['UF_EXT_KEY'] + 1;

        $object->set('UF_EXT_KEY', $UF_EXT_KEY);
    }

    public function ifCreateNew($fields,$object)
    {
        // можно ли создавать планирование без денег
        /*
        if($fields['UF_MONEY_RESERVE'] > 0){}
        else{
            throw new FulfiException("У Вас недостаточно средств для создания исполнений");
        }
        */
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $taskManager = $sL->get('Kabinet.Task');
        $TaskData = $taskManager->getData(true,[],['ID'=>$fields['UF_TASK_ID']]);
        $TaskData = $TaskData[0];
        if  (count($TaskData['UF_TARGET_SITE']) == 1) {
            $site_link = $TaskData['UF_TARGET_SITE'][0]['VALUE'];
            $object->set('UF_LINK', $site_link);
        }

    }

    public function ifChangeStatus($id,$primary,$fields,$object,$oldData){
		if (!$fields['UF_STATUS']) return;
		
        $newStatus = $fields['UF_STATUS'];
        $oldStatus = $oldData['UF_STATUS'];

		
        if ($oldStatus != $newStatus) {
            $object->set('UF_CREATE_DATE', new \Bitrix\Main\Type\DateTime());

            $newState = $this->makeState(array_merge($fields,['ID'=>$id['ID']]));

            if($newState->conditionsTransition($oldData)){
                $oldState = $this->makeState($oldData);
                $oldState->leaveStage($object);
                $newState->cameTo($object);
            }

        }

        //throw new FulfiException("Test Stop1");
    }
	
	/*
	Если пользователь отклоняет и пишет комментарий, то так же он отправляется в чат менеджеру
	*/
    public function CommentifChange($id,$primary,$fields,$object,$oldData){
        $manager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
		
        $new = $fields['UF_COMMENT'];
        $old = $oldData['UF_COMMENT'];

        if ($old == '' && $new != '') {
            $task = \Bitrix\Kabinet\task\datamanager\TaskTable::getById($oldData['UF_TASK_ID'])->fetch();
           // создать сообщение в чате
		   $manager->add([
			   'UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::USER_MESSAGE,
			   'UF_TASK_ID'=>$oldData['UF_TASK_ID'],
			   'UF_PROJECT_ID'=>$task['UF_PROJECT_ID'],
			   'UF_TARGET_USER_ID'=>$task['UF_MANAGER_ID'],
			   'UF_QUEUE_ID'=>$oldData['ID'],
			   'UF_MESSAGE_TEXT'=>"В задаче ".$task['UF_NAME']." пользователь отклонил с комментарием: ".$new,
			   'UF_UPLOADFILE' => [],
		   ]);
        }
        //throw new FulfiException("Test Stop1");
    }	

    public function historyChangeStatus($id,$primary,$fields,$object,$oldData){
        if (!$fields['UF_STATUS']) return;

        $newStatus = $fields['UF_STATUS'];
        $oldStatus = $oldData['UF_STATUS'];

        if ($oldStatus != $newStatus) {
            $this->addHistoryChangeStatus($fields,$object,$oldData);
        }

    }

    public function OnBeforeDeleteHandler($id, $primary, $oldFields)
    {
        $manager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
        // ищим все сообщения
        $HLMess = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('LMESSANGER_HL');
        $filter['UF_QUEUE_ID'] = $id;
        $listdata = $HLMess::getlist(['select'=>['*'], 'filter' => $filter])->fetchAll();
        foreach($listdata as $item){
            $manager->delete($item['ID']);
        }
    }

    public function checkTask(array $task){
        $BillingManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
        $userBilling = $BillingManager->getData();
        $TaskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');
        $PRODUCT = $TaskManager->getProductByTask($task);

        $GLOBALS['aaa'] = 'y';
        $dateEnd = $TaskManager->getItem($task)->theorDateEnd($task);


        if (!$task['UF_TARGET_SITE'][0]['VALUE']) throw new FulfiException("Вы не заполнили обязательное поле Ссылка!");
        if (!$task['UF_CYCLICALITY']) throw new FulfiException("Вы не выбрали Цикличность задачи!");
        if ($task['UF_CYCLICALITY'] == 1 && empty($task['UF_DATE_COMPLETION'])) throw new FulfiException("Не выбрана дата завершения!");


        // дата завершения не может быть теоретической
        $interval = $task['UF_DATE_COMPLETION'] - $dateEnd->getTimestamp();

        //поскольку выбираем только день, то разница в минутах и секундах может быть,
        // при сравнении это влияет,
        // поэтому предполагаем что эта разница не больше 86300
        if ($interval < -86300 && $task['UF_CYCLICALITY'] !=2 && $task['UF_CYCLICALITY'] !=33) throw new FulfiException("Дата завершения не може быть меньше ".$dateEnd->format("d.m.Y"));

        if (!$task['UF_NUMBER_STARTS']) throw new FulfiException("Не выбранно количество!");


        // максимальное количество в месяц
        if (
            $task['UF_CYCLICALITY'] == 2 &&
            $PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE'] &&
            $task['UF_NUMBER_STARTS']>$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']
        ) throw new FulfiException("Вы привысили максимальное количество ".$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']." ед. в месяц");

        // Минимальное количество для заказа
        if (
            $PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE'] &&
            $task['UF_NUMBER_STARTS']<$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']
        ) throw new FulfiException("Минимальное количество для заказа ".$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']. ' ед.');
        
		/*
		Наличее сретств проверяется сразу и не дает создать планирование
		*/
        //if ($task['FINALE_PRICE'] > $userBilling['UF_VALUE']) throw new FulfiException("У Вас недостаточно срудств для выполнение операции".$userBilling['UF_VALUE']);

        return true;
    }


    // /cron/cron1.php запускается в футере
    // \Bitrix\Kabinet\task\Autorun::run()
    // ищет все задачи с 'UF_CYCLICALITY' =>[2,34] и
    public function startTask($task){

        //throw new TestException("Временно не доступно! В разработке!");

        $this->taskFileds = $task;
        $this->checkTask($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $BillingManager = $sL->get('Kabinet.Billing');

        $taskObject = $TaskManager->getItem($task);
        $PlannedDate = $taskObject->PlannedPublicationDate($task);

        $FINALE_PRICE = $taskObject->calcPlannedFinalePrice($task,$PlannedDate);

        $isError = $BillingManager->getMoney($FINALE_PRICE,$task['UF_AUTHOR_ID'],$this);
        // $isError === false, потому что $isError возвращает введеную сумму и когда у задачи 0 руб. то при сравнении !$isError возникает ошибка
        if ($isError === false) throw new FulfiException("Недостаточно средств для выполнения задачи. Пополните баланс и запустите задачу.");

        $taskObject->createFulfi($task,$PlannedDate);
    }

    public function stopTask($task){
        //throw new FulfiException("Временно не доступно! В разработке!");
        $countStopTask = 0;
        $datalist = $this->getData($task['ID']);
        foreach ($datalist as $fields){
            if ($fields['UF_STATUS'] == 10) continue;
            $State = $this->makeState($fields);
            $allowStatus = $State->getRoutes();
            if (in_array(10,$allowStatus)){

                $fields['UF_STATUS'] = 10;
                $this->update($fields);
                $countStopTask++;
            }
        }

        if ($task['UF_CYCLICALITY'] != 2 && $task['UF_CYCLICALITY'] != 34){
                if (!$countStopTask) throw new FulfiException("Нет исполнений которые можно остановить. Дождитесь выполнения задач.");
        }

        return $countStopTask;
    }
	
	public function getQueue($task){
		$Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
		$list = $Queue->getQueue($task['ID']);
		
		foreach($list as $k => &$v){
			unset($v['UF_OPERATION']);
		}
		
		return $list;
	}

    public function remakeFulfiData(array $Queue){
        $listdata = [];
        foreach ($Queue as $data) {
            $c = $this->convertData($data, $this->getUserFields());
            // используется в отоюражении календаря
            $c['UF_STATUS_ORIGINAL'] = $this->currentStatus($c);

            //$type = ($c['UF_ELEMENT_TYPE']=='')? $c['UF_ELEMENT_TYPE']:'review';
            //$c['STATUSLIST'] = $this->getStatus($type);
            $c['STATUSLIST'] = $this->allowStates($c);

            // кастомезируем названия статусов доступные для клиента
            if (!\PHelp::isAdmin()){
                array_walk($c['STATUSLIST'],function (&$item, $key,$c){
                    // 4 - В работе у специалиста
                    if ($item['ID'] == 4 && $c['UF_STATUS'] != 3) $item['TITLE'] = 'Отклонить с комментарием';
                    // 4 - Публикация
                    if ($item['ID'] == 6) $item['TITLE'] = '<i class="fa fa-rocket" aria-hidden="true"></i>Отправить на публикацию';
                    // 9 - Выполнена
                    if ($item['ID'] == 9) $item['TITLE'] = 'Отчет принят';
                },$c);
            }

            if ($c['UF_HISTORYCHANGE']) $c['UF_HISTORYCHANGE_ORIGINAL'] = unserialize($c['UF_HISTORYCHANGE']);
            else $c['UF_HISTORYCHANGE_ORIGINAL'] = [];

            $listdata[] = $c;
        }

        return $listdata;
    }

    public function getTaskFulfiData(int $task){
        $select = $this->getSelectFields();
        $Queue = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
            'select'=>$select,
            'filter'=>['UF_TASK_ID'=>$task],
            //'order' => ['ID'=>'DESC'],
        ])->fetchAll();

        //\Dbg::print_r($Queue);
        $listdata = $this->remakeFulfiData($Queue);

        return $listdata;
    }

    public function getIDFulfiData(int $id){
        $select = $this->getSelectFields();
        $Queue = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
            'select'=>$select,
            'filter'=>['ID'=>$id],
            //'order' => ['ID'=>'DESC'],
        ])->fetchAll();

        //\Dbg::print_r($Queue);
        $listdata = $this->remakeFulfiData($Queue);

        return $listdata[0];
    }

    public function getData($task_id_list,$clear=false,$id=[],$filter=[]){
        global $CACHE_MANAGER;

        if (!$task_id_list && !$id && !$filter) return [];

        //$requestURL = $user_id;
        //$cacheSalt = md5($requestURL);
        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($task_id_list);
        $cacheId .= "|".serialize($id);
		$cacheId .= "|".serialize($filter);

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать

        $ttl = 0;

        $cache = new \CPHPCache;
        // Clear cache "queue_data"
        if ($clear) $cache->clean($cacheId, "kabinet/queue");
        //$CACHE_MANAGER->ClearByTag("queue_data");

        $cache->clean($cacheId, "kabinet/queue");

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/queue"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("queue_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

           
            if ($task_id_list) $filter['UF_TASK_ID'] = $task_id_list;
            elseif($id) $filter['ID'] = $id;
		

            if (!$filter) throw new FulfiException("Фильтр для исполнений не определен");

            $select = $this->getSelectFields();
            $HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
            $Queue = $HLBClass::getlist([
                'select'=>$select,
                'filter'=>$filter,
                //'order' => ['ID'=>'DESC'],
            ])->fetchAll();
            
            //\Dbg::print_r($Queue);
            $listdata = $this->remakeFulfiData($Queue);

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

    public function getStatusList(){
        $status = $this->config('STATUS');
        return $status['LIST'];
    }

    public function makeState($fields){		
        if (!$fields['UF_ELEMENT_TYPE']) throw new FulfiException("Отсутствует тип. Невозможно создать стадию.");
        $type = $fields['UF_ELEMENT_TYPE'];
        $status = $fields['UF_STATUS'];
        $list = $this->getStatus($type);
        $key = array_search($status, array_column($list, 'ID'));
        if ($key === false) throw new FulfiException("Ошибка при определение разрешенных статусов.");

        $nameState = $list[$key]['NAME'];

        //new \Bitrix\Kabinet\taskrunner\states\Xmlload
        $state = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('states')->$type()->$nameState($fields);
		
        return $state;
    }

    public function getStatus($type){
        $list = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('states')->getQueuStatus($type);
        return $list;
    }

    public function allowStates($fields){
        $filtered = [];
        if (!$fields['UF_ELEMENT_TYPE']) return [];
        $status = $fields['UF_STATUS'];
        $list = $this->getStatus($fields['UF_ELEMENT_TYPE']);

        /*
        $key = array_search($status, array_column($list, 'ID'));
        if ($key === false) throw new FulfiException("Ошибка при определение разрешенных статусов.");
        $filtered[] = $list[$key];
        */

        $state = $this->makeState($fields);
        $allow = $state->getRoutes();

        foreach ($list as $item)
            if (in_array($item['ID'], $allow )) $filtered[] = $item;

        return  $filtered;
    }

    public function currentStatus($runner){
        $filtered = ['VALUE'=>$runner['UF_STATUS_ORIGINAL'],'CSS'=>$this->getStatusCss($runner['UF_STATUS_ORIGINAL'])];

        $status = $runner['UF_STATUS'];
        $list = $this->getStatus($runner['UF_ELEMENT_TYPE']);

        $key = array_search($status, array_column($list, 'ID'));
        if ($key === false) {
			//var_dump([$status,$runner['ID'],$list,$runner['UF_ELEMENT_TYPE']]);
			throw new FulfiException("Ошибка при определение разрешенных статусов.");
			}
        $filtered = array_merge($filtered,$list[$key]);

        return $filtered;
    }

    public function getStatusCss(int $id){
        $status = $this->config('STATUS');
        return $status['CSS'][$id];
    }

    protected function addHistoryChangeStatus($fields,$object,$oldData){
        $siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
        $history = [];

        $newState = $this->makeState($fields);
        $oldState = $this->makeState($oldData);

        if ($oldData['UF_HISTORYCHANGE']) $history = unserialize($oldData['UF_HISTORYCHANGE']);

        $history[] = [
            'DATE_CHANGE' => (new \Bitrix\Main\Type\DateTime())->format("d.m.Y H:i:s"),
            'USER_CHANGE_ID' => $siteuser->get('ID'),
            'USER_CHANGE' => $siteuser->printName(),
            'OLD_STATUS_ID' => $oldData['UF_STATUS'],
            'NEW_STATUS_ID' => $object->get('UF_STATUS'),
            'OLD_STATUS_TITLE' => $oldState->getTitle(),
            'NEW_STATUS_TITLE' => $newState->getTitle(),
        ];

        $object->set('UF_HISTORYCHANGE',serialize($history));
    }
	
	public function toArchive($task){
		$HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');
		$archive = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('ARCHIVE');
		
		$data = $HLBClass::getlist([
			'select' => ['*'],
			'filter'=>[
					'UF_TASK_ID'=>$task['ID'],
					'UF_ACTIVE'=>1,
					'UF_STATUS'=>[0,9,10],
					],
		])->fetchAll();
		
		$ret = [];
		foreach($data as $fields){
            $ret[] = $archive->add($this,$fields);
		}
		
		return $ret;
	}
}
