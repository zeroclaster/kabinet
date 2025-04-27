<?
namespace Bitrix\Kabinet\task\type;

use \Bitrix\Main\SystemException;

class Cyclicality extends Itemtask{
    private $subtype;

    private $cyclicality = 0;
    private $description = <<<TZ

        Базовый расчет для цикличных задач

TZ;

    public function __construct(Itemtask $tasktype)
    {
        $this->subtype = $tasktype;
    }

    public function startItemTask($task){
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task,$PlannedDate);

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $dateStar = $this->subtype->dateStartTask($task);

        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $today = new \Bitrix\Main\Type\DateTime();
        if($task['UF_STATUS']>0) {
            // ищем последнее исполнение
            $db_array = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->FulfiCache($task);

            if ($db_array) {
                [$firstDayNextMonth, $lastDayNextMonth] = \PHelp::concretenextMonth($db_array[0]['UF_PLANNE_DATE']);
                // что бы дата старта не попала в прошлое
                if ($db_array[0]['UF_PLANNE_DATE'] > $today) return $firstDayNextMonth;

            }
        }

        // ЕСЛИ ЗАДАЧА НОВАЯ!

        $today->add($PRODUCT['DELAY_EXECUTION']['VALUE']." hours");
        [$firstDayNextMonth,$lastDayNextMonth] = \PHelp::concretenextMonth($today);

        if ($today->getTimestamp() > $firstDayNextMonth->getTimestamp()) return $firstDayNextMonth->add($PRODUCT['DELAY_EXECUTION']['VALUE'] . " hours");

        return $today;
    }

    public function theorDateEnd($task){
        $dateEnd = $this->subtype->theorDateEnd($task);

        // дата начала
        $dateStart = $this->dateStartTask($task);
        [$mouthStart1,$mouthEnd1] = \PHelp::concreteMonth($dateStart);

        return $mouthEnd1;
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $dateStar = $this->dateStartTask($task);
        $dateEnd = $this->theorDateEnd($task);

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

    /*
    public function theorDateEnd__($task){
        $dateEnd = $this->subtype->theorDateEnd($task);

        // дата начала
        $dateStart = $this->dateStartTask($task);
        [$mouthStart1,$mouthEnd1] = \PHelp::concreteMonth($dateStart);
        [$mouthStart2,$mouthEnd2] = \PHelp::concretenextMonth($dateStart);

        if($task['UF_STATUS']>0) return $mouthEnd1;

        // Если задача еще не начата
        if ($dateStart->getTimestamp() <= $mouthEnd1->getTimestamp()) return $mouthEnd1;
        else return $mouthEnd2;
    }
    */

    /*
    public function PlannedPublicationDate__($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $dateStar = $this->dateStartTask($task);
        $dateEnd = $this->theorDateEnd($task);

        $dateList = [$dateStar];

        $UF_NUMBER_STARTS = $task['UF_NUMBER_STARTS'] - 1;
        if ($UF_NUMBER_STARTS == 0)  return $dateList;

        //Day of the month without leading zeros
        if ((new \Bitrix\Main\Type\DateTime())->format("m") == $dateEnd->format("m"))
            $currentDay = (new \Bitrix\Main\Type\DateTime())->format("d");
        else
            $currentDay = $dateStar->format("d");

        $lastDayMonth = $dateEnd->format("d");

        // Если задача не начата
        if ($task['UF_STATUS']==0){
            $d = $lastDayMonth - $currentDay + 1;
            $h = $d*24;

            // +1 что появился интервал до первого исполнения след. месяца.
            $step_ = floor($h / ($task['UF_NUMBER_STARTS']));

        }
        // Если задача начата
        else{
            // +1 что появился интервал до первого исполнения след. месяца.
            $step_ = floor($lastDayMonth*24 / ($task['UF_NUMBER_STARTS']));
        }


        $step = max($PRODUCT['MINIMUM_INTERVAL']['VALUE'],$step_);

        for ($i = 0; $i < $UF_NUMBER_STARTS; $i++) {
            $calcDaysStep = $step * ($i + 1);

            // постоянно прибавляем к стартовому значению шаг умноженный на позицию
            $newObjectDate = clone $dateStar;
            $calcDate = $newObjectDate->add("+" . $calcDaysStep . ' hours');
            if ($calcDate->getTimestamp() > $dateEnd) break;
            $dateList[$i + 1] = $calcDate;
        }

        return $dateList;
    }
    */

    public function createFulfi($task,$PlannedDate){
        $this->subtype->createFulfi($task,$PlannedDate);
    }
}