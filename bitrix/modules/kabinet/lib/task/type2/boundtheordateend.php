<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Boundtheordateend{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function theorDateEnd($task){
        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);

        // ВЫсчитываем сколько займет задача в часах КОЛИЧЕСТВО * МИН ИНТЕРВАЛ МЕЖДУ ИСПОЛНЕНИЯМИ
        // Если задача не начата
        if(!$task['UF_STATUS']) {
            $hours = ($task['UF_NUMBER_STARTS'] - 1) * $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
            return $this->parent->dateStartTask($task)->add($hours." hours");
        }else {
            $hours = ($task['UF_NUMBER_STARTS'] - 1) * $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
            return $this->parent->dateStartTask($task)->add($hours." hours");
        }

    }
}