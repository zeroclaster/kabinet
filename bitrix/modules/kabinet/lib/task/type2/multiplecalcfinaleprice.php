<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Multiplecalcfinaleprice{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $FINALE_PRICE = $task['FINALE_PRICE'];
        return $FINALE_PRICE;
    }
}