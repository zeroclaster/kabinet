<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Multipleplannedpublicationdate{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function PlannedPublicationDate($task){
        $dateStar = $this->parent->dateStartTask($task);
        $dateList[] = \PHelp::BitrixdateNow($dateStar);

        return $dateList;
    }
}