<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Calcfinaleprice{
    private $parent;

    public function setConteiner($parent){
        $this->parent = $parent;
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $onePrice = $task['FINALE_PRICE'] / $task['UF_NUMBER_STARTS'];
        $FINALE_PRICE = $onePrice*count($PlannedDate);

        return $FINALE_PRICE;
    }
}