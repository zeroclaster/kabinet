<?
namespace Bitrix\Kabinet\task\type;


class Cyclicality_34 extends Cyclicality{
    private $subtype;

    private $cyclicality = 34;
    private $description = <<<TZ

        Ежемесячная услуга

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

        $now = new \Bitrix\Main\Type\DateTime();
        [$startMonth, $endMonth]= \PHelp::concreteMonth($now);
        $day = $now->format("d");
        $day2 = $endMonth->format("d");

        $FINALE_PRICE = $onePrice * ( ($day2 - $day) / $day2 );

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $calc_date = $this->subtype->dateStartTask($task);

        return $calc_date;
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