<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

namespace Bitrix\Kabinet\task;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\Exceptions\TaskException,
    \Bitrix\Kabinet\Exceptions\TestException,
    \Bitrix\Main\Entity;

class Taskmanager extends \Bitrix\Kabinet\container\Hlbase {

    const NO_APPROVAL_REQUIRED = 9;
    const LINK_SCREENHOT = 10;
    // из свойств инфоблока поле COORDINATION
    const IS_SOGLACOVANIE = 'cd3e95f3415f08e2ff1d8e9cb16e9d1d'; // да

    // из свойств инфоблока поле PHOTO_AVAILABILITY
    const PHOTO_NO_NEEDED = '07e891d703f65d59109cc89571177b39';

    // статусы задачи
    const STOPPED = 14;
    const WORKED = 15;
    const PAUSE = 16;

    public $productList = [];

    //Возможность непрерывности задачи
    // из каталога
    protected $TASK_CONTINUITY = [
        '5f08a50f317495840fe150a6556e3d43' =>[33],    //Одно исполнение
        '9295af06d671d06eb0bf036c3886f9d3' =>[1],   //Только однократные
        '4e6662937b21b89d5c02879b7e47718b' =>[2],   //Непрерывная задача
        '51e37ecf0978bf080600464552b95d1f'=>[1,2],  //Однократная или непрерывная
        'fb226d4fc4447d5c81e2a902042ffca3' =>[34],   //Ежемесячная услуга
    ];
    protected $user;

    public function __construct($user, $HLBCClass,$config=[],$runnermanager=null)
    {
        $this->config = $config;
        $this->user = $user;

        parent::__construct($HLBCClass);

        AddEventHandler("", "\Task::OnBeforeAdd", [$this,"OnBeforeAddHandler"]);
        AddEventHandler("", "\Task::OnBeforeAdd", [$this,"AutoIncrementAddHandler"]);
        AddEventHandler("", "\Task::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);
        AddEventHandler("", "\Task::OnAfterDelete", [$this,"OnAfterDeleteHandler"]);

        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'ifChangeCycliality'],100);
        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'checkBeforeUpdate'],200);
        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'ifChangeNumberCycliality'],300);
    }


    public function AutoIncrementAddHandler($fields,$object)
    {
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');
        $last = $HLBClass::getlist([
            'select'=>['UF_EXT_KEY'],
            'order'=>['ID'=>"DESC"],
            'limit' =>1
        ])->fetch();

        $UF_EXT_KEY = 200000;
        if ($last && $last['UF_EXT_KEY']>0) $UF_EXT_KEY = $last['UF_EXT_KEY'] + 1;

        $object->set('UF_EXT_KEY', $UF_EXT_KEY);
    }

    public function ifChangeCycliality($id,$primary,$fields,$object,$oldData){
        if (!$fields['UF_CYCLICALITY']) return;

        $new = $fields['UF_CYCLICALITY'];
        $old = $oldData['UF_CYCLICALITY'];

        if ($old != $new) {
            $object->set('UF_DATE_COMPLETION', null);
        }

        //throw new TestException("Test Stop1");
    }

    public function ifChangeNumberCycliality($id,$primary,$fields,$object,$oldData){
        if (!$fields['UF_CYCLICALITY']) return;


        $new = $fields;
        $old = $oldData;

        if ($new['UF_CYCLICALITY'] == 1) {
            // обновляем дату завершения новой расчетной датой если изменилось колличество или тип цикличность
            if ($old['UF_CYCLICALITY'] != $new['UF_CYCLICALITY']) {
                $DATE_COMPLETION = $this->getItem($new)->theorDateEnd($new);
                $object->set('UF_DATE_COMPLETION', $DATE_COMPLETION);
            }

            if ($old['UF_NUMBER_STARTS'] != $new['UF_NUMBER_STARTS']) {

                $PRODUCT = $this->getProductByTask($fields);

                // 2025-02-13 Если выбран вариант «равномерно до заданной даты», то надо поле количество уже не надо проверять на привышение максимума
                /*
                if($new['UF_NUMBER_STARTS'] > $PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE'])
                    throw new TaskException("Максимальное количество в месяц ".$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']);
                */

                // 22.04.2025
                //if (\Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION)->getTimestamp() > $old['UF_DATE_COMPLETION']->getTimestamp())
                //    $object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));

                if($new['UF_NUMBER_STARTS'] < $PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE'])
                    throw new TaskException("Минимальное количество для заказа ".$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']);

                // 14.02.2025
                // было до этого, дата всегда менялась если сменить количество
                //$DATE_COMPLETION = $this->getItem($new)->theorDateEnd($new);
                //$object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));
            }
        }
    }

    public function checkBeforeUpdate($id,$primary,$fields,$object,$oldData){
        if (
            $fields['UF_CYCLICALITY'] == 1 &&
            $fields['UF_DATE_COMPLETION'] &&
            $oldData['UF_NUMBER_STARTS']==$fields['UF_NUMBER_STARTS']
        ) {
            $DATE_COMPLETION = $this->getItem($oldData)->theorDateEnd($oldData);

            if (\PHelp::compareDates($fields['UF_DATE_COMPLETION'], $DATE_COMPLETION))
                throw new TaskException("Дата завершения не может быть меньше ".$DATE_COMPLETION->format('d.m.Y'));
        }
    }

    // Схема создания новой задачи
    public function OnBeforeAddHandler($fields,$object)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        $ProjectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');

        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('BRIEF_HL');
        $isExistsProject = $HLBClass::getById($fields["UF_PROJECT_ID"])->fetch();
        if (!$isExistsProject) throw new TaskException("Invalid field value UF_PROJECT_ID");

        // не даем создавать задачу, если нет такого продукта
        $isExists = \Bitrix\Iblock\ElementTable::getById($fields["UF_PRODUKT_ID"])->fetch();
        if (!$isExists) throw new TaskException("Invalid field value UF_PRODUKT_ID");

        $object->set('UF_NAME',$isExists['NAME']);

        // устанавливаем цикличность задачи
        // если у задачи один вариант то сразу его выбираем!
        $orders = $ProjectManager->orderData($fields['UF_AUTHOR_ID']);
        $PRODUCT = $orders[$isExistsProject['UF_ORDER_ID']][$fields["UF_PRODUKT_ID"]];

        // IBLOCK  TASK_CONTINUITY - Возможность непрерывности задачи
        if ($PRODUCT['TASK_CONTINUITY']['VALUE_XML_ID']) {
            // доступные варианты для задачи
            $possible_options = $this->TASK_CONTINUITY[$PRODUCT['TASK_CONTINUITY']['VALUE_XML_ID']];
            $isOneRun = count($possible_options)==1;
            if ($isOneRun) {
                // для сохранения в базу
                $object->set('UF_CYCLICALITY', $possible_options[0]);
                //Одно исполнение
                if ($possible_options[0] == 33) $object->set('UF_NUMBER_STARTS', 1);
                //Ежемесячная услуга
                if ($possible_options[0] == 34) $object->set('UF_NUMBER_STARTS', 1);

                // делаем массив задачи что бы его отправить на расчет даты завершения
                $fields_ = $fields;
                $fields_['UF_CYCLICALITY'] = $possible_options[0];
                //Одно исполнение
                if ($possible_options[0] == 33) $fields_['UF_NUMBER_STARTS'] = 1;
                //Ежемесячная услуга
                if ($possible_options[0] == 34) $fields_['UF_NUMBER_STARTS'] = 1;
            }else{
                $object->set('UF_NUMBER_STARTS', $PRODUCT['QUANTITY']);
                $fields_ = $fields;
                $fields_['UF_NUMBER_STARTS'] = $PRODUCT['QUANTITY'];
                $fields_['UF_CYCLICALITY'] = 1;
            }

            $DATE_COMPLETION = $this->getItem($fields_)->theorDateEnd($fields_);
            $object->set('UF_DATE_COMPLETION', $DATE_COMPLETION);
        }

    }

    public function OnBeforeDeleteHandler($id, $primary, $oldFields)
    {
        $RunnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');
        $filter['UF_TASK_ID'] = $id;
        $listdata = $HLBClass::getlist(['select' => ['*'], 'filter'=>$filter])->fetchAll();
        foreach($listdata as $item){
            $RunnerManager->delete($item['ID']);
        }
    }

    public function OnAfterDeleteHandler($id, $primary, $oldFields)
    {
    }

    public function remakeData(array $source){
        $listdata = [];
        foreach ($source as $data) {
            $dataconvert = $this->convertData($data, $this->getUserFields());

            $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MINDATE'] = $dataconvert['UF_DATE_COMPLETION'];
            $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MAXDATE'] = (new \Bitrix\Main\Type\DateTime())->add("+1 year")->getTimestamp();

            if ($dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MINDATE'] > $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MAXDATE']) $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MAXDATE'] = $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MINDATE'];

            $listdata[] = $dataconvert;
        }

        foreach ($listdata as $index => $task2) {
            $taskObject = $this->getItem($task2);

            $PRODUCT = $this->getProductByTask($task2);
            if (!$PRODUCT){
                unset($listdata[$index]);
                continue;
            }

            if (empty($listdata[$index]['UF_NUMBER_STARTS'])) $listdata[$index]['UF_NUMBER_STARTS'] = $PRODUCT['QUANTITY'];

            $listdata[$index]['FINALE_PRICE'] = $listdata[$index]['UF_NUMBER_STARTS'] * $PRODUCT['CATALOG_PRICE_1'];

            $listdata[$index]['QUEUE_STATIST'] = $this->getQueueStatistics($task2);
            //$listdata[$index]['QUEUE_STATIST'] = [];


            // Цикличность задачи
            if ($PRODUCT['TASK_CONTINUITY']['VALUE_XML_ID']){
                $possible_options = $this->TASK_CONTINUITY[$PRODUCT['TASK_CONTINUITY']['VALUE_XML_ID']];
                foreach ($task2['UF_CYCLICALITY_ORIGINAL'] as $k1223 => $v1234){
                    if (!in_array($v1234['ID'],$possible_options)) unset($task2['UF_CYCLICALITY_ORIGINAL'][$k1223]);
                }
            }

            // 1 - Однократное выполнение
            // 2 - Повторяется ежемесячно
            foreach($task2['UF_CYCLICALITY_ORIGINAL'] as $k1223 => $v1234){
                if  ($v1234['VALUE'] == '') continue;

                // 1 - Однократное выполнение
                if ($v1234['ID'] == 1) {
                    $task2['UF_CYCLICALITY_ORIGINAL'][$k1223]['VALUE'] = 'равномерно с ' . $taskObject->dateStartTask(array_merge($task2,['UF_CYCLICALITY'=>1]))->format("d.m.Y") . ' до заданной даты';
                }

                // 2 - Повторяется ежемесячно
                if ($v1234['ID'] == 2) {
                    $task2['UF_CYCLICALITY_ORIGINAL'][$k1223]['VALUE'] = 'ежемесячно, начиная с ' . $taskObject->dateStartTask(array_merge($task2,['UF_CYCLICALITY'=>2]))->format("d.m.Y");
                }
            }


            $listdata[$index]['UF_CYCLICALITY_ORIGINAL'] = $task2['UF_CYCLICALITY_ORIGINAL'];

            // Ежемесячная услуга
            if ($task2['UF_CYCLICALITY'] == 34) $d = $taskObject->dateStartTask($task2);

            // Ежемесячное выполнение
            if ($task2['UF_CYCLICALITY'] == 2) $d = $taskObject->dateStartTask($task2);

            // до заданной даты
            if ($task2['UF_CYCLICALITY'] == 1 || $task2['UF_CYCLICALITY'] == 33) $d = $this->dateEndOne($task2);

            $listdata[$index]['RUN_DATE'] = $d->format("d.m.Y");
        }

        return $listdata;
    }

    public function getData($clear=false,$user_id = [],$filter=[]){
        global $CACHE_MANAGER;

        if (!$user_id){
            $user = $this->user;
            $user_id = $user->get('ID');
        }

        // сколько времени кешировать
        $ttl = 14400;


        // hack: $ttl = 0 то не кешировать
        // $ttl = 0 отменяем чтение из кеша
        // function initCache $ttl <= 0 return false;
        if (is_array($user_id)) $ttl = 0;

        // Кеш завязан только на пользователе
        // любой update вызывает clearCache() $this->getData($clear=true);
        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($user_id);

        // hack: $ttl = 0 то не кешировать
        // $ttl = 0 отменяем чтение из кеша
        // function initCache $ttl <= 0 return false;
        if ($filter) $ttl = 0;
        if (!$filter) $filter = ['UF_AUTHOR_ID'=>$user_id];
        $ttl = 0;

        $cache = new \CPHPCache;

        // Clear cache "task_data"
        if ($clear) $cache->clean($cacheId, "kabinet/task");
        //$CACHE_MANAGER->ClearByTag("task_data");

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/task"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("task_data");
            }

            $dataSQL = \Bitrix\Kabinet\task\datamanager\TaskTable::getListActive([
                'select'=>['*'],
                'filter'=>$filter,
                'order'=>["ID"=>'ASC']
            ])->fetchAll();

            //echo \Bitrix\Main\Entity\Query::getLastQuery();

            foreach ($dataSQL as $key => $data) {
                $dataSQL[$key]['UF_DATE_COMPLETION'] = $this->getItem($data)->theorDateEnd($data);
            }

            $listdata = $this->remakeData($dataSQL);

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

    public function dateEndOne($task){
        // если задача уже выполняется, то дата начало это последнее исполнение
        if($task['UF_STATUS']>0){
            $db_array = $this->FulfiCache($task);
            if ($db_array) return $db_array[0]['UF_PLANNE_DATE'];
        }

        return new \Bitrix\Main\Type\DateTime;
    }

    public function getProductByTask(array $task){

        $PRODUKT_ID = $task['UF_PRODUKT_ID'];

        // cache afect!
        //if ( isset($this->productList[$PRODUKT_ID]) ) return $this->productList[$PRODUKT_ID];

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ProjectManager = $sL->get('Kabinet.Project');
        $briefs =$ProjectManager->getData(false,$task['UF_AUTHOR_ID']);
        $orders =$ProjectManager->orderData($task['UF_AUTHOR_ID']);

        $key = array_search($task['UF_PROJECT_ID'], array_column($briefs, 'ID'));
        if ($key === false) throw new TaskException("Не найден проект с ID ".$task['UF_PROJECT_ID']);
        $UF_ORDER_ID = $briefs[$key]['UF_ORDER_ID'];


        if (empty($orders[$UF_ORDER_ID][$PRODUKT_ID])) {
            throw new TaskException("Не найден продукт с ID ".$PRODUKT_ID. ' в заказе '. $UF_ORDER_ID);
            $this->delete($task['ID']);
            return false;
        }
        $PRODUCT = $orders[$UF_ORDER_ID][$PRODUKT_ID];

        $this->productList[$PRODUKT_ID] = $PRODUCT;

        return $PRODUCT;
    }

    public function getTaskById(int $id){
        $collection = $this->getData();
        $key = array_search($id, array_column($collection, 'ID'));
        if ($key === false) return false;

        return $collection[$key];
    }

    public function getQueueStatistics($task){
        $ret = [];

        $status = 0;
        $dbArray = $this->FulfiCache($task);
        $Queue = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = [1,2,3,4,5,6,7,8];
        $Queue = [];
        foreach ($dbArray as $item)  if (in_array($item['UF_STATUS'],$status)) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }

        $ret[] = $st;

        $status = 9;
        $Queue = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;


        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = 10;
        $Queue = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;


        // 5 - На согласовании (у клиента)
        // 8 - Отчет на проверке у клиента
        $status = [5,8];
        $Queue = [];
        foreach ($dbArray as $item)  if (in_array($item['UF_STATUS'],$status)) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }

        $ret[] = $st;

        return $ret;
    }

    public function FulfiCache($task){
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');

        $taskId = $task['ID'];
        $id = $task['UF_AUTHOR_ID'];

        if (!$this->FulfiCacheArray[$id]) {
            $this->FulfiCacheArray[$id] = [];
            $data = $HLBClass::getlist([
                'select' => ['ID', 'UF_PLANNE_DATE', 'REF_TASK.ID', 'UF_ELEMENT_TYPE', 'UF_NUMBER_STARTS', 'UF_STATUS', 'UF_DATE_COMPLETION'],
                'filter' => [
                    'REF_TASK.UF_AUTHOR_ID'=> $id
                ],
                'runtime' => [
                    'REF_TASK' => [
                        'data_type' => \Task::class,
                        'reference' => ['=this.UF_TASK_ID' => 'ref.ID'],
                        'join_type' => 'INNER'
                    ]
                ],
                'order' => ['UF_PLANNE_DATE' => 'desc'],
            ])->fetchAll();

            foreach ($data as $key => $item){
                foreach ($item as $field => $value){
                    if (is_object($value) && ($value instanceof \Bitrix\Main\Type\DateTime) && $value)
                        $this->FulfiCacheArray[$id][$key][$field . '_TIMESTAMP'] = $value->getTimestamp();

                    $this->FulfiCacheArray[$id][$key][$field] = $value;
                }
            }
        }

        $ret = [];
        foreach ($this->FulfiCacheArray[$id] as $key =>  $item) if ($item['FULFILLMENT_REF_TASK_ID'] == $taskId) {
            foreach ($item as $field => $value){
                if (is_object($value) && ($value instanceof \Bitrix\Main\Type\DateTime) && $value) {
                    $item[$field] = \Bitrix\Main\Type\DateTime::createFromTimestamp($item[$field . '_TIMESTAMP']);
                }
            }
            $ret[]=$item;
        }

        return $ret;
    }

    public function toArchive($task){
        $archive = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('ARCHIVE');
        return $archive->add($this,$task);
    }

    public function getItem($task){
        $Itemfactory = new \Bitrix\Kabinet\task\factory\Itemfactory2;
        $item = $Itemfactory->getObject($task);

        return $item;
    }
}

