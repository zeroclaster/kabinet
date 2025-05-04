<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Cycliacalityplannedpublicationdate{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function PlannedPublicationDate($task){
        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $dateStar = $this->parent->dateStartTask($task);
        $dateEnd = $this->parent->theorDateEnd($task);

        $dateList = [$dateStar];

        $UF_NUMBER_STARTS = $task['UF_NUMBER_STARTS'] - 1;
        if ($UF_NUMBER_STARTS == 0)  return $dateList;


        // Если задача не начата
        if ($task['UF_STATUS']==0){
            $d = $dateEnd->format("d") - $dateStar->format("d") + 1;
            $h = $d*24;

            // +1 что появился интервал до первого исполнения след. месяца.
            $step_ = floor($h / ($task['UF_NUMBER_STARTS']));

        }
        // Если задача начата
        else{
            // +1 что появился интервал до первого исполнения след. месяца.
            $step_ = floor($dateEnd->format("d") * 24 / ($task['UF_NUMBER_STARTS']));
        }


        $step = max($PRODUCT['MINIMUM_INTERVAL']['VALUE'],$step_);

        for ($i = 0; $i < $UF_NUMBER_STARTS; $i++) {
            $calcDaysStep = $step * ($i + 1);

            // постоянно прибавляем к стартовому значению шаг умноженный на позицию
            $newObjectDate = clone $dateStar;
            $calcDate = $newObjectDate->add("+" . $calcDaysStep . ' hours');
            if ($calcDate->getTimestamp() > $dateEnd->getTimestamp()) break;
            $dateList[$i + 1] = $calcDate;
        }

        return $dateList;
    }
}