<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */


namespace Bitrix\Kabinet\task;

use \Bitrix\Main\SystemException,
    \Bitrix\Main\Entity;

class Taskmanagercache extends Taskmanager {


    protected function FulfiCache($taskId){

        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

        if (!$this->FulfiCacheArray) {
            $this->FulfiCacheArray = $HLBClass::getlist([
                'select' => ['ID', 'UF_PLANNE_DATE','REF_TASK.ID','UF_ELEMENT_TYPE','UF_NUMBER_STARTS','UF_STATUS','UF_DATE_COMPLETION'],
                'runtime' => [
                    'REF_TASK' => [
                        'data_type' => \Task::class,
                        'reference' => ['=this.UF_TASK_ID' => 'ref.ID'],
                        'join_type' => 'INNER'
                    ]
                ],
                'order' => ['UF_PLANNE_DATE' => 'desc'],
            ])->fetchAll();
        }

        $ret = [];
        foreach ($this->FulfiCacheArray as $item) if ($item['FULFILLMENT_REF_TASK_ID'] == $taskId) $ret[]=$item;

        return $ret;
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

            $db_array = $this->FulfiCache($task['ID']);
            $find_last_queue = [];
            if ($db_array) $find_last_queue = $db_array[0];

            if ($find_last_queue) {
                if ($find_last_queue['UF_DATE_COMPLETION']){
                    if ($find_last_queue['UF_DATE_COMPLETION']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_DATE_COMPLETION'];
                }else {
                    if ($find_last_queue['UF_PLANNE_DATE']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_PLANNE_DATE'];
                }

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



    public function getQueueStatistics(int $id){
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

        $ret = [];

        $status = 0;
        $dbArray = $this->FulfiCache($id);
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

        return $ret;
    }
}
