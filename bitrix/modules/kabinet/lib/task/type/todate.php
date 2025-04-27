<?
namespace Bitrix\Kabinet\task\type;

use \Bitrix\Main\SystemException;

class Todate extends Itemtask{
    private $subtype;

    private $cyclicality = 0;
    private $description = <<<TZ

   
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

        // + задержка исполнения
        if($task['UF_STATUS']==0) return $today->add($PRODUCT['DELAY_EXECUTION']['VALUE'] . " hours");

        // ищем последнее исполнение
        $db_array = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->FulfiCache($task);
        // что бы дата старта не попала в прошлое
        if ($db_array && ($db_array[0]['UF_PLANNE_DATE'] > $today)) return $db_array[0]['UF_PLANNE_DATE']->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");


        // минимальный интервал исполнения
        return $today->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
    }

    public function theorDateEnd($task){
        $dateEnd = $this->subtype->theorDateEnd($task);

        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);

        // ВЫсчитываем сколько займет задача в часах КОЛИЧЕСТВО * МИН ИНТЕРВАЛ МЕЖДУ ИСПОЛНЕНИЯМИ
        $hours = ($task['UF_NUMBER_STARTS']-1) * $PRODUCT['MINIMUM_INTERVAL']['VALUE'];

        // Если задача начата, то вычитаем MINIMUM_INTERVAL
        if($task['UF_STATUS']>0) $hours = $hours - $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
        return $this->dateStartTask($task)->add($hours." hours");
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        $PRODUCT = (\Bitrix\Main\DI\ServiceLocator::getInstance())->get('Kabinet.Task')->getProductByTask($task);
        $dateStar = $this->dateStartTask($task);

        $dateList = [\PHelp::BitrixdateNow($dateStar)];
        $UF_NUMBER_STARTS = $task['UF_NUMBER_STARTS'] - 1;
        if ($UF_NUMBER_STARTS == 0) return $dateList;

        $diffDays = $dateStar->getDiff(\Bitrix\Main\Type\DateTime::createFromTimestamp($task['UF_DATE_COMPLETION']))->format('%a');

        // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения
        $step = floor($diffDays * 24 / $task['UF_NUMBER_STARTS']);
        $step = max($PRODUCT['MINIMUM_INTERVAL']['VALUE'], $step);

        for ($i = 0; $i < $UF_NUMBER_STARTS; $i++) {
            $calcDaysStep = $step * ($i + 1);
            $now = \PHelp::BitrixdateNow($dateStar);
            $dateList[$i+1] = $now->add("+" . $calcDaysStep . ' hours');
        }

        return $dateList;
    }

    public function createFulfi($task,$PlannedDate){
        $this->subtype->createFulfi($task,$PlannedDate);
    }
}