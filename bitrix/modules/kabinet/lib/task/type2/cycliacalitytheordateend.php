<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Cycliacalitytheordateend{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function theorDateEnd($task){
        // дата начала
        $dateStart = $this->parent->dateStartTask($task);
        [$mouthStart1,$mouthEnd1] = \PHelp::concreteMonth($dateStart);

        return $mouthEnd1;
    }
}