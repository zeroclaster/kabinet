<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Boundplannedpublicationdate{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function PlannedPublicationDate($task){
        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $dateStar = $this->parent->dateStartTask($task);

        $dateList = [\PHelp::BitrixdateNow($dateStar)];

        $UF_NUMBER_STARTS = $task['UF_NUMBER_STARTS'] - 1;
        if ($UF_NUMBER_STARTS == 0) return $dateList;

        $diffDays = $dateStar->getDiff(\Bitrix\Main\Type\DateTime::createFromTimestamp($task['UF_DATE_COMPLETION']))->format('%a');

        // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения
        $step = floor($diffDays * 24 / ($task['UF_NUMBER_STARTS']-1));
        $step = max($PRODUCT['MINIMUM_INTERVAL']['VALUE'], $step);

        for ($i = 0; $i < $UF_NUMBER_STARTS; $i++) {
            $calcDaysStep = $step * ($i + 1);
            $now = \PHelp::BitrixdateNow($dateStar);
            $dateList[$i+1] = $now->add("+" . $calcDaysStep . ' hours');
        }

        return $dateList;
    }
}