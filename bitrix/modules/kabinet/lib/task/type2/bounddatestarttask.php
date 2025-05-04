<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Bounddatestarttask{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function dateStartTask($task){
        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $today = new \Bitrix\Main\Type\DateTime();

        // + задержка исполнения
        if($task['UF_STATUS']==0) return $today->add($PRODUCT['DELAY_EXECUTION']['VALUE'] . " hours");

        // ищем последнее исполнение
        $db_array = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->FulfiCache($task);
        // что бы дата старта не попала в прошлое
        if ($db_array && ($db_array[0]['UF_PLANNE_DATE'] > $today)) return $db_array[0]['UF_PLANNE_DATE']->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");


        // минимальный интервал исполнения
        return $today->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
    }
}