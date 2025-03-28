<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

namespace Bitrix\Kabinet\task;

use \Bitrix\Main\SystemException,
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

    public function __construct(int $id, $HLBCClass)
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new SystemException("Сritical error! Registered users only.");

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Task::OnBeforeAdd", [$this,"OnBeforeAddHandler"]);
        AddEventHandler("", "\Task::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);
        AddEventHandler("", "\Task::OnAfterDelete", [$this,"OnAfterDeleteHandler"]);

        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'ifChangeCycliality'],100);
        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'checkBeforeUpdate'],200);
        AddEventHandler("", "\Task::OnBeforeUpdate", [$this,'ifChangeNumberCycliality'],300);
    }

    public function ifChangeCycliality($id,$primary,$fields,$object,$oldData){
        if (!$fields['UF_CYCLICALITY']) return;

        $new = $fields['UF_CYCLICALITY'];
        $old = $oldData['UF_CYCLICALITY'];

        if ($old != $new) {
            $object->set('UF_DATE_COMPLETION', null);
        }

        //throw new SystemException("Test Stop1");
    }

    public function ifChangeNumberCycliality($id,$primary,$fields,$object,$oldData){
        if (!$fields['UF_CYCLICALITY']) return;


        $new = $fields;
        $old = $oldData;

        if ($new['UF_CYCLICALITY'] == 1) {
            // обновляем дату завершения новой расчетной датой если изменилось колличество или тип цикличность
            if ($old['UF_CYCLICALITY'] != $new['UF_CYCLICALITY']) {
                $DATE_COMPLETION = $this->theorDateEnd($new);
                $object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));
            }

            if ($old['UF_NUMBER_STARTS'] != $new['UF_NUMBER_STARTS']) {

                $PRODUCT = $this->getProductByTask($fields);

                // 2025-02-13 Если выбран вариант «равномерно до заданной даты», то надо поле количество уже не надо проверять на привышение максимума
                /*
                if($new['UF_NUMBER_STARTS'] > $PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE'])
                    throw new SystemException("Максимальное количество в месяц ".$PRODUCT['MAXIMUM_QUANTITY_MONTH']['VALUE']);
                */
                $DATE_COMPLETION = $this->theorDateEnd($new);
                if (\Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION)->getTimestamp() > $old['UF_DATE_COMPLETION']->getTimestamp())
                    $object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));

                if($new['UF_NUMBER_STARTS'] < $PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE'])
                    throw new SystemException("Минимальное количество для заказа ".$PRODUCT['MINIMUM_QUANTITY_MONTH']['VALUE']);

                // 14.02.2025
                // было до этого, дата всегда менялась если сменить количество
                //$DATE_COMPLETION = $this->theorDateEnd($new);
                //$object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));
            }
        }

        //throw new SystemException("Test Stop1");
    }

    public function checkBeforeUpdate($id,$primary,$fields,$object,$oldData){
        if (
            $fields['UF_CYCLICALITY'] == 1 &&
            $fields['UF_DATE_COMPLETION'] &&
            $oldData['UF_NUMBER_STARTS']==$fields['UF_NUMBER_STARTS']
        ) {
            $DATE_COMPLETION = $this->theorDateEnd($oldData);


            //throw new SystemException($fields['UF_DATE_COMPLETION']->format('d.m.Y H:i:s'));

            // if ($fields['UF_DATE_COMPLETION']->getTimestamp() < $DATE_COMPLETION)
            if (\PHelp::compareDates($fields['UF_DATE_COMPLETION'], \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION)))
                throw new SystemException("Дата завершения не может быть меньше ".\Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION)->format('d.m.Y'));
        }

        //throw new SystemException("Test Stop1");
    }

    // Схема создания новой задачи
    public function OnBeforeAddHandler($fields,$object)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ProjectManager = $sL->get('Kabinet.Project');

        $HLBClass = (\KContainer::getInstance())->get(BRIEF_HL);
        $isExistsProject = $HLBClass::getById($fields["UF_PROJECT_ID"])->fetch();
        if (!$isExistsProject) throw new SystemException("Invalid field value UF_PROJECT_ID");

        // не даем создавать задачу, если нет такого продукта
        $isExists = \Bitrix\Iblock\ElementTable::getById($fields["UF_PRODUKT_ID"])->fetch();
        if (!$isExists) throw new SystemException("Invalid field value UF_PRODUKT_ID");

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
            }

            $DATE_COMPLETION = $this->theorDateEnd($fields_);
            $object->set('UF_DATE_COMPLETION', \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION));
        }

    }

    public function OnBeforeDeleteHandler($id, $primary, $oldFields)
    {

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
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

            // TODO AKULA глюки с сохранением даты
            if (!$data['UF_DATE_COMPLETION']) {
                $DATE_COMPLETION = $this->theorDateEnd($data);
                $data['UF_DATE_COMPLETION'] = \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION);
            }

            // Одно исполнение и задача еще не выполняется
            if ($data['UF_CYCLICALITY'] == 33 && $data['UF_STATUS']==0) {
                $data['UF_DATE_COMPLETION'] = \Bitrix\Main\Type\DateTime::createFromTimestamp($this->theorDateEnd($data));
            }

            $dataconvert = $this->convertData($data, $this->getUserFields());

            $DATE_COMPLETION = $this->theorDateEnd($dataconvert);
            //$d = \Bitrix\Main\Type\DateTime::createFromTimestamp($DATE_COMPLETION);
            //$d->add("1 day");
            //$dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MINDATE'] = (new \Bitrix\Main\Type\DateTime($d->format("d.m.Y 00:00:00"),"d.m.Y H:i:s"))->getTimestamp();
            $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MINDATE'] = $DATE_COMPLETION;
            $dataconvert['UF_DATE_COMPLETION_ORIGINAL']['MAXDATE'] = (new \Bitrix\Main\Type\DateTime())->add("+1 year")->getTimestamp();;
            $listdata[] = $dataconvert;
        }

        foreach ($listdata as $index => $task2) {
            $PRODUCT = $this->getProductByTask($task2);
            if (!$PRODUCT){
                unset($listdata[$index]);
                continue;
            }

            if (empty($listdata[$index]['UF_NUMBER_STARTS']))
                $listdata[$index]['UF_NUMBER_STARTS'] = $PRODUCT['QUANTITY'];

            $listdata[$index]['FINALE_PRICE'] = $listdata[$index]['UF_NUMBER_STARTS'] * $PRODUCT['CATALOG_PRICE_1'];

            $listdata[$index]['QUEUE_STATIST'] = $this->getQueueStatistics($task2['ID']);
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
                    $d = $this->dateStartOne(array_merge($task2,['UF_CYCLICALITY'=>1]));
                    $date1 = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

                    $task2['UF_CYCLICALITY_ORIGINAL'][$k1223]['VALUE'] = 'равномерно с ' . $date1->format("d.m.Y") . ' до заданной даты';
                }

                // 2 - Повторяется ежемесячно
                if ($v1234['ID'] == 2) {
                    $d = $this->dateStartCicle(array_merge($task2,['UF_CYCLICALITY'=>2]));
                    $date1 = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

                    $task2['UF_CYCLICALITY_ORIGINAL'][$k1223]['VALUE'] = 'ежемесячно, начиная с ' . $date1->format("d.m.Y");
                }

                // Одно исполнение
                if ($v1234['ID'] == 33) {

                }
            }


            $listdata[$index]['UF_CYCLICALITY_ORIGINAL'] = $task2['UF_CYCLICALITY_ORIGINAL'];

            // Ежемесячная услуга
            if ($task2['UF_CYCLICALITY'] == 34) {
                $d = $this->dateStartCicle($task2);
                $listdata[$index]['RUN_DATE'] = \Bitrix\Main\Type\DateTime::createFromTimestamp($d)->format("d.m.Y");
            }


            if ($task2['UF_CYCLICALITY'] == 2) {
                $d = $this->dateStartCicle($task2);
                $listdata[$index]['RUN_DATE'] = \Bitrix\Main\Type\DateTime::createFromTimestamp($d)->add("1 month")->format("d.m.Y");
            }
        }

        return $listdata;
    }

    public function getData($clear=false,$user_id = [],$filter=[]){
        global $CACHE_MANAGER;

        if (!$user_id){
            $user = (\KContainer::getInstance())->get('user');
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

    public function theorDateEnd(array $task){
        $PRODUCT = $this->getProductByTask($task);

        if(
            (
                //Однократное выполнение
                $task['UF_CYCLICALITY'] == 1 ||
                // Одно исполнение
                $task['UF_CYCLICALITY'] == 33)
            // 12.02.2025
            // задачи с одним исполнение Минимальный интервал планирования = 0, и из-за этого попадали в ежемес. исполнения
           // &&
            //$PRODUCT['MINIMUM_INTERVAL']['VALUE']
        ){
            $dateTimestamp = $this->dateStartOne($task);

            // ВЫсчитываем сколько займет задача в часах КОЛИЧЕСТВО * МИН ИНТЕРВАЛ МЕЖДУ ИСПОЛНЕНИЯМИ
            $hours = $task['UF_NUMBER_STARTS'] * $PRODUCT['MINIMUM_INTERVAL']['VALUE'];

            // Если задача начата, то вычитаем MINIMUM_INTERVAL
            if($task['UF_STATUS']>0) $hours = $hours - $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
            $DATE_COMPLETION = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateTimestamp)->add($hours." hours")->getTimestamp();
        }else{
            // дата начала
            $dateTimestamp = $this->dateStartCicle($task);
            [$mouthStart1,$mouthEnd1] = \PHelp::actualMonth();
            [$mouthStart2,$mouthEnd2] = \PHelp::nextMonth();

            $DATE_COMPLETION = $mouthEnd2->getTimestamp();

            // Если задача еще не начата
            if($task['UF_STATUS']==0)
                if ($dateTimestamp <= $mouthEnd1->getTimestamp()) $DATE_COMPLETION = $mouthEnd1->getTimestamp();

        }

        return $DATE_COMPLETION;
    }

    public function dateStartOne($task){
        $PRODUCT = $this->getProductByTask($task);

        // "Задержка исполнения"
        if (empty($PRODUCT['DELAY_EXECUTION']['VALUE'])){
            //TADO тестовое значение задержки исполнения
            $PRODUCT['DELAY_EXECUTION']['VALUE'] = 72;
        }

        $now = new \Bitrix\Main\Type\DateTime();

        // если задача уже выполняется, то дата начало это последнее исполнение
        if($task['UF_STATUS']>0){
            $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
            // ищем последнее исполнение
            $find_last_queue = $HLBClass::getlist([
                'select'=>['ID','UF_PLANNE_DATE','UF_DATE_COMPLETION'],
                'filter'=>['UF_TASK_ID'=>$task['ID']],
                'order'=>['UF_PLANNE_DATE'=>'desc'],
                'limit'=>1
            ])->fetch();

            if ($find_last_queue) {

                if ($find_last_queue['UF_DATE_COMPLETION']){
                    if ($find_last_queue['UF_DATE_COMPLETION']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_DATE_COMPLETION'];
                }else {
                    if ($find_last_queue['UF_PLANNE_DATE']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_PLANNE_DATE'];
                }

                // + $PRODUCT['MINIMUM_INTERVAL']['VALUE']
                if ($PRODUCT['MINIMUM_INTERVAL']['VALUE'])
                    $now->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
            }else
                // минимальный интервал исполнения
                $now->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
        }else {
            // задержка исполнения
            $now->add($PRODUCT['DELAY_EXECUTION']['VALUE'] . " hours");
        }

        return $now->getTimestamp();
    }

    public function dateStartCicle($task){
        $PRODUCT = $this->getProductByTask($task);

        // "Задержка исполнения"
        if (empty($PRODUCT['DELAY_EXECUTION']['VALUE'])){
            //TADO тестовое значение задержки исполнения
            $PRODUCT['DELAY_EXECUTION']['VALUE'] = 72;
        }

        // если задача циклическая, есть задержка исполнения
        if ($task['UF_CYCLICALITY'] == 2) $DELAY_EXECUTION = $PRODUCT['DELAY_EXECUTION']['VALUE'];
        else $DELAY_EXECUTION = 0;

        $now = (new \Bitrix\Main\Type\DateTime)->add($DELAY_EXECUTION." hours");
        $firstDayNextMonth = new \Bitrix\Main\Type\DateTime((new \DateTime('first day of next month'))->format("d.m.Y 00:00:01"), "d.m.Y H:i:s");

        if ($now > $firstDayNextMonth)
            $calc_date = $firstDayNextMonth->add($DELAY_EXECUTION . " hours")->getTimestamp();
        else
            $calc_date = $now->getTimestamp();

        return $calc_date;
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
        if ($key === false) throw new SystemException("Не найден проект с ID ".$task['UF_PROJECT_ID']);
        $UF_ORDER_ID = $briefs[$key]['UF_ORDER_ID'];

        if (empty($orders[$UF_ORDER_ID][$PRODUKT_ID])) {
            throw new SystemException("Не найден продукт с ID ".$PRODUKT_ID. ' в заказе '. $UF_ORDER_ID);
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

    public function getQueueStatistics__(int $id){
        return [];
    }

    public function getQueueStatistics(int $id){
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

        $ret = [];

        $status = 0;
        $Queue = $HLBClass::getlist([
            'select'=>['ID','UF_ELEMENT_TYPE','UF_NUMBER_STARTS'],
            'filter'=>[
                '=UF_TASK_ID'=>$id,
                'UF_STATUS'=>$status
            ]
        ])->fetchAll();


        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = [1,2,3,4,5,6,7,8];
        $Queue = $HLBClass::getlist([
            'select'=>['ID','UF_ELEMENT_TYPE','UF_NUMBER_STARTS'],
            'filter'=>[
                '=UF_TASK_ID'=>$id,
                'UF_STATUS'=>$status
            ]
        ])->fetchAll();

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }

        $ret[] = $st;

        $status = 9;
        $Queue = $HLBClass::getlist([
            'select'=>['ID','UF_ELEMENT_TYPE','UF_NUMBER_STARTS'],
            'filter'=>[
                '=UF_TASK_ID'=>$id,
                'UF_STATUS'=>$status
            ]
        ])->fetchAll();

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = 10;
        $Queue = $HLBClass::getlist([
            'select'=>['ID','UF_ELEMENT_TYPE','UF_NUMBER_STARTS'],
            'filter'=>[
                '=UF_TASK_ID'=>$id,
                'UF_STATUS'=>$status
            ]
        ])->fetchAll();

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

    public function toArchive($task){
        $archive = (\KContainer::getInstance())->get('ARCHIVE');
        return $archive->add($this,$task);

    }
}
