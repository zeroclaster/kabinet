<?
namespace Bitrix\Kabinet\task\type;


class Multipleitem extends Itemtask{
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
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task);
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
        $dateList_ = $this->subtype->PlannedPublicationDate($task);

        $dateStar = $this->dateStartTask($task);
        $dateList[] = \PHelp::BitrixdateNow($dateStar);

        return $dateList;
    }
}