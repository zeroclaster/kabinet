<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Cycliacalitycalcfinaleprice_34{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $onePrice = $task['FINALE_PRICE'] / $task['UF_NUMBER_STARTS'];

        $today = new \Bitrix\Main\Type\DateTime();
        [$startMonth, $endMonth]= \PHelp::concreteMonth($today);
        $day = $today->format("d");
        $day2 = $endMonth->format("d");

        $FINALE_PRICE = $onePrice * ( ($day2 - $day) / $day2 );

        return $FINALE_PRICE;
    }
}