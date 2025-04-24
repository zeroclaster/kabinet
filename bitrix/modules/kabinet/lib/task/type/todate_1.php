<?
namespace Bitrix\Kabinet\task\type;


class Todate_1 extends Todate{
    private $subtype;

    private $cyclicality = 1;
    private $description = <<<TZ

       Однократное выполнение

TZ;


    public function __construct(Itemtask $tasktype)
    {
        $this->subtype = $tasktype;
    }

    public function startItemTask($task){
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task);

        $FINALE_PRICE = $task['FINALE_PRICE'];
        $onePrice = $FINALE_PRICE / $task['UF_NUMBER_STARTS'];

        $FINALE_PRICE = $onePrice*count($PlannedDate);

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $dateStar = $this->subtype->dateStartTask($task);

        return $dateStar;
    }

    public function theorDateEnd(array $task){
        $DATE_COMPLETION = $this->subtype->theorDateEnd($task);

        return $DATE_COMPLETION;
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        return $dateList;
    }
}