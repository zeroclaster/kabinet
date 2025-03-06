<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

namespace Bitrix\Kabinet\taskrunner;

use \Bitrix\Main\SystemException,
    \Bitrix\Main\Type\Datetime;

class Runnermanager extends \Bitrix\Kabinet\container\Hlbase{
    public $status = [
        0=>"Запланировано, но не начато",
        1=>"Взят в работу",
        2=>"Пишется текст",
        3=>"Ожидается текст от клиента",
        4=>"На согласовании у специалиста",
        5=>"На согласование (у клиента)",
        6=>"Публикация",
        7=>"Отчет на проверке специалиста",
        8=>"Отчет на проверке у клиента",
        9=>"Выполнено",
        10=>"Отменено"
    ];

    public $statusCSS = [
        0=>"fc-event-warning",
        1=>"fc-event-success",
        2=>"fc-event-success",
        3=>"fc-event-success",
        4=>"fc-event-success",
        5=>"fc-event-success",
        6=>"fc-event-success",
        7=>"fc-event-success",
        8=>"fc-event-success",
        9=>"fc-event-light",
        10=>"fc-event-danger"
    ];

    public $taskFileds = [];

    public function __construct(int $id, $HLBCClass,array $selectFields,$config=[])
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new SystemException("Сritical error! Registered users only.");
		
		$this->config = $config;

        $this->selectFields = $selectFields;

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Fulfillment::OnBeforeAdd", [$this,'ifCreateNew'],100);
        AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'ifChangeStatus'],100);
		AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'CommentifChange'],150);
        AddEventHandler("", "\Fulfillment::OnBeforeUpdate", [$this,'historyChangeStatus'],200);
        AddEventHandler("", "\Fulfillment::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);
    }

    public function ifCreateNew($fields,$object)
    {
        // можно ли создавать планирование без денег
        /*
        if($fields['UF_MONEY_RESERVE'] > 0){}
        else{
            throw new SystemException("У Вас недостаточно средств для создания исполнений");
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

        //throw new SystemException("Test Stop1");
    }
	
	/*
	Если пользователь отклоняет и пишет комментарий, то так же он отправляется в чат менеджеру
	*/
    public function CommentifChange($id,$primary,$fields,$object,$oldData){		
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $manager = $sL->get('Kabinet.Messanger');
		
        $new = $fields['UF_COMMENT'];
        $old = $oldData['UF_COMMENT'];

		
        if ($old == '' && $new != '') {
		   
		   $HLTask = (\KContainer::getInstance())->get(TASK_HL);
		   $task = $HLTask::getlist(['select'=>['*'],'filter'=>['ID'=>$oldData['UF_TASK_ID']],'limit'=>1])->fetch();
		   
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

        //throw new SystemException("Test Stop1");
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

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $manager = $sL->get('Kabinet.Messanger');
        // ищим все сообщения
        $HLMess = (\KContainer::getInstance())->get(LMESSANGER_HL);
        $filter['UF_QUEUE_ID'] = $id;
        $listdata = $HLMess::getlist(['select'=>['*'], 'filter' => $filter])->fetchAll();
        foreach($listdata as $item){
            $manager->delete($item['ID']);
        }
    }

    public function checkTask(array $task){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $BillingManager = $sL->get('Kabinet.Billing');
        $userBilling = $BillingManager->getData();
        $TaskManager = $sL->get('Kabinet.Task');
        $PRODUCT = $TaskManager->getProductByTask($task);
        $dateEnd = $TaskManager->theorDateEnd($task);



        if (!$task['UF_TARGET_SITE'][0]['VALUE']) throw new SystemException("Вы не заполнили обязательное поле Ссылка!");

        if (!$task['UF_CYCLICALITY']) throw new SystemException("Вы не выбрали Цикличность задачи!");
        if ($task['UF_CYCLICALITY'] == 1 && empty($task['UF_DATE_COMPLETION'])) throw new SystemException("Не выбрана дата завершения!");
        $interval = $task['UF_DATE_COMPLETION'] - $dateEnd;

        //поскольку выбираем только день, то разница в минутах и секундах может быть,
        // при сравнении это влияет,
        // поэтому предполагаем что эта разница не больше 86300
        if ($interval < -86300 && $task['UF_CYCLICALITY'] !=2) throw new SystemException("Дата завершения не може быть меньше ".DateTime::createFromTimestamp($dateEnd)->format("d.m.Y"));

        if (!$task['UF_NUMBER_STARTS']) throw new SystemException("Не выбранно количество!");


        // максимальное количество в месяц
        if (
            $PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE'] &&
            $task['UF_NUMBER_STARTS']>$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']
        ) throw new SystemException("Вы привысили максимальное количество ".$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']." шт. в месяц");

        // минимальное количество в месяц
        if (
            $PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE'] &&
            $task['UF_NUMBER_STARTS']<$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']
        ) throw new SystemException("Минимальное количество в месяц ".$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']. ' шт.');


		/*
		Наличее сретств проверяется сразу и не дает создать планирование
		*/
        //if ($task['FINALE_PRICE'] > $userBilling['UF_VALUE']) throw new SystemException("У Вас недостаточно срудств для выполнение операции".$userBilling['UF_VALUE']);

        return true;
    }

    public function MultiplePlannedPublicationDate($task){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        if($task['UF_CYCLICALITY'] == 1 || $task['UF_CYCLICALITY'] == 33)
                    $dateStar = $TaskManager->dateStartOne($task);
        else
                    $dateStar = $TaskManager->dateStartCicle($task);

        $dateList[] = \PHelp::BitrixdateNow($dateStar);

        return $dateList;
    }

    public function PlannedPublicationDate($task){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        $dateStar = $TaskManager->dateStartOne($task);
        $now = \PHelp::dateNow($dateStar);

        $task['UF_NUMBER_STARTS'] = $task['UF_NUMBER_STARTS'] - 1;
        $dateList = [];
        $dateList[] = \PHelp::BitrixdateNow($dateStar);
        if ($task['UF_NUMBER_STARTS'] > 0) {
            $diffDays = $now->diff(\DateTime::createFromFormat('U', $task['UF_DATE_COMPLETION']))->format('%a');
            // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения
            $step = floor($diffDays / $task['UF_NUMBER_STARTS']);


            for ($i = 0; $i < $task['UF_NUMBER_STARTS']; $i++) {
                $calcDaysStep = $step * ($i + 1);
                $now = \PHelp::BitrixdateNow($dateStar);
                $dateList[$i+1] = $now->add("+" . $calcDaysStep . ' days');
            }
        }

        return $dateList;
    }

    public function CiclePlannedPublicationDate($task){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        [$mouthStart1,$mouthEnd1] = \PHelp::actualMonth();
        [$mouthStart2,$mouthEnd2] = \PHelp::nextMonth();

        $dateStar = $TaskManager->dateStartCicle($task);
        $dateEnd = $TaskManager->theorDateEnd($task);

        $mouthStart = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateStar);
        $dateList = [];
        $dateList[] = $mouthStart;
        $task['UF_NUMBER_STARTS'] = $task['UF_NUMBER_STARTS'] - 1;
        if ($task['UF_CYCLICALITY'] == 2 && $task['UF_NUMBER_STARTS'] > 0) {
            // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения
            $step = floor(30 / $task['UF_NUMBER_STARTS']);
        }else{
            $step = 1;
        }

        $mStart =  $mouthStart2->getTimestamp();
        // Если задача еще не начата
        if($task['UF_STATUS']==0) $mStart = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateStar)->getTimestamp();

        if ($task['UF_NUMBER_STARTS'] > 0) {
            for ($i = 0; $i < $task['UF_NUMBER_STARTS']; $i++) {
               $calcDaysStep = $step * ($i + 1);

               // постоянно прибавляем к стартовому значению шаг умноженный на позицию
               $calcDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($mStart)->add("+" . $calcDaysStep . ' days');
               if ($calcDate->getTimestamp() > $dateEnd) break;
               $dateList[$i + 1] = $calcDate;
            }
        }

        return $dateList;
    }

    // /cron/cron1.php запускается в футере
    // \Bitrix\Kabinet\task\Autorun::run()
    // ищет все задачи с 'UF_CYCLICALITY' =>[2,34] и
    public function startTask($task){

        //throw new SystemException("Временно не доступно! В разработке!");

        $this->taskFileds = $task;
        $this->checkTask($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $BillingManager = $sL->get('Kabinet.Billing');
        $PRODUCT = $TaskManager->getProductByTask($task);

        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

        //Тип услуги
        //$type = $PRODUCT['TYRE_SERVICE']['VALUE'];

        //Элемент тип
        $type = $PRODUCT['ELEMENT_TYPE']['VALUE'];

        $FINALE_PRICE = $task['FINALE_PRICE'];
        $onePrice = $FINALE_PRICE / $task['UF_NUMBER_STARTS'];

        // если задача множественная, то есть одно исполнение и установленное количество
        if ($type == 'multiple') {
            $PlannedDate = $this->MultiplePlannedPublicationDate($task);

            $isGetMoney = $BillingManager->getMoney($FINALE_PRICE,$task['UF_AUTHOR_ID'],$this);
            if (!$isGetMoney) throw new SystemException("Недостаточно средств для выполнения задачи. Пополните баланс и запустите задачу.");

            $obResult = $HLBClass::add([
                'UF_TASK_ID' => $task['ID'],
                'UF_ELEMENT_TYPE' => $type,
                'UF_CREATE_DATE' => new \Bitrix\Main\Type\DateTime(),
                'UF_PLANNE_DATE' => $PlannedDate[0],
                'UF_MONEY_RESERVE' => $FINALE_PRICE,
            ]);
            if (!$obResult->isSuccess()) {
                $err = $obResult->getErrors();
                throw new SystemException("Ошибка при создании планирования. " . $err[0]->getMessage());
            }

            $ID = $obResult->getID();

        }else {


            // цикличность задачи
            // 1 - Однократное выполнение
            // 2 - Повторяется ежемесячно
            // 33 - Одно исполнение
            if ($task['UF_CYCLICALITY'] == 1 || $task['UF_CYCLICALITY'] == 33)
                $PlannedDate = $this->PlannedPublicationDate($task);
            else
                $PlannedDate = $this->CiclePlannedPublicationDate($task);

            $FINALE_PRICE = $onePrice*count($PlannedDate);

            $isGetMoney = $BillingManager->getMoney($FINALE_PRICE,$task['UF_AUTHOR_ID'],$this);
            if ($isGetMoney === false) throw new SystemException("Недостаточно средств для выполнения задачи. Пополните баланс и запустите задачу.");

            for ($i = 0; $i < count($PlannedDate); $i++) {
                $obResult = $HLBClass::add([
                    'UF_TASK_ID' => $task['ID'],
                    'UF_ELEMENT_TYPE' => $type,
                    'UF_CREATE_DATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_PLANNE_DATE' => $PlannedDate[$i],
                    'UF_MONEY_RESERVE' => $onePrice,
                ]);
                if (!$obResult->isSuccess()) {
                    $err = $obResult->getErrors();
                    throw new SystemException("Ошибка при создании планирования. " . $err[0]->getMessage());
                }

                $ID = $obResult->getID();

                /*
                Наличее сретств проверяется сразу и не дает создать планирование
                */
                /*
                $money = $BillingManager->getMoney($onePrice);
                $obResult = $HLBClass::update($ID,['UF_MONEY_RESERVE'=>$money]);
                if (!$obResult->isSuccess()){
                    $err = $obResult->getErrors();
                    throw new SystemException("Ошибка при резервировании денежных средств. ".$err[0]->getMessage());
                }
                */

                //$state = (\KContainer::getInstance())->get('states')->$type()->state1($ID);
                //$Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
                //$Queue->completeStage($state);
            }
        }
    }

    public function stopTask($task){
        //throw new SystemException("Временно не доступно! В разработке!");
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
                if (!$countStopTask) throw new SystemException("Нет исполнений которые можно остановить. Дождитесь выполнения задач.");
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
		

            if (!$filter) throw new SystemException("Фильтр для исполнений не определен");

            $select = $this->getSelectFields();
            $HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
            $Queue = $HLBClass::getlist([
                'select'=>$select,
                'filter'=>$filter,
                //'order' => ['ID'=>'DESC'],
            ])->fetchAll();


            //\Dbg::print_r($Queue);

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
                            if ($item['ID'] == 6) $item['TITLE'] = 'Согласовано, опубликовать';
                            // 9 - Выполнена
                            if ($item['ID'] == 9) $item['TITLE'] = 'Отчет принят';
                        },$c);
                }

                if ($c['UF_HISTORYCHANGE']) $c['UF_HISTORYCHANGE_ORIGINAL'] = unserialize($c['UF_HISTORYCHANGE']);
                else $c['UF_HISTORYCHANGE_ORIGINAL'] = [];

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

    public function getStatusList(){
        return $this->status;
    }

    public function makeState($fields){		
        if (!$fields['UF_ELEMENT_TYPE']) throw new SystemException("Отсутствует тип. Невозможно создать стадию.");
        $type = $fields['UF_ELEMENT_TYPE'];
        $status = $fields['UF_STATUS'];
        $ID = $fields['ID'];
        $list = $this->getStatus($type);
        $key = array_search($status, array_column($list, 'ID'));
        if ($key === false) throw new SystemException("Ошибка при определение разрешенных статусов.");

        $nameState = $list[$key]['NAME'];

        //new \Bitrix\Kabinet\taskrunner\states\Xmlload
        $state = (\KContainer::getInstance())->get('states')->$type()->$nameState($fields);
		
        return $state;
    }

    public function getStatus($type){
        $list = (\KContainer::getInstance())->get('states')->getQueuStatus($type);
        return $list;
    }

    public function allowStates($fields){
        $filtered = [];
        if (!$fields['UF_ELEMENT_TYPE']) return [];
        $status = $fields['UF_STATUS'];
        $list = $this->getStatus($fields['UF_ELEMENT_TYPE']);

        /*
        $key = array_search($status, array_column($list, 'ID'));
        if ($key === false) throw new SystemException("Ошибка при определение разрешенных статусов.");
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
			throw new SystemException("Ошибка при определение разрешенных статусов.");
			}
        $filtered = array_merge($filtered,$list[$key]);

        return $filtered;
    }

    public function getStatusCss(int $id){
        return $this->statusCSS[$id];
    }

    protected function addHistoryChangeStatus($fields,$object,$oldData){
        $siteuser = (\KContainer::getInstance())->get('siteuser');
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
		$HLBClass = (\KContainer::getInstance())->get('FULF_HL');
		
		$archive = (\KContainer::getInstance())->get('ARCHIVE');
		
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
