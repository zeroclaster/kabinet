<?
namespace Bitrix\Kabinet\task\type;

use \Bitrix\Main\SystemException;

class Cyclicality_2 extends Cyclicality{
    private $subtype;

    private $cyclicality = 2;
    private $description = <<<TZ

        Задача выполняется кждый месяц

TZ;


    public function __construct(Itemtask $tasktype)
    {
        $this->subtype = $tasktype;
    }

    public function startItemTask($task){
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task,$PlannedDate);

        $onePrice = $task['FINALE_PRICE'] / $task['UF_NUMBER_STARTS'];
        $FINALE_PRICE = $onePrice*count($PlannedDate);

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $calc_date = $this->subtype->dateStartTask($task);

        return $calc_date;
    }

    public function theorDateEnd($task){
        $DATE_COMPLETION = $this->subtype->theorDateEnd($task);

        return $DATE_COMPLETION;
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        return $dateList;
    }

    public function createFulfi($task,$PlannedDate){
        $this->subtype->createFulfi($task,$PlannedDate);
    }
}