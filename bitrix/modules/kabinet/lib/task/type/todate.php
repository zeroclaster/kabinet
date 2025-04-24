<?
namespace Bitrix\Kabinet\task\type;


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
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task);

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $dateStar = $this->subtype->dateStartTask($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        $PRODUCT = $this->getProductByTask($task);

        // "Задержка исполнения"
        if (empty($PRODUCT['DELAY_EXECUTION']['VALUE'])){
            //TADO тестовое значение задержки исполнения
            $PRODUCT['DELAY_EXECUTION']['VALUE'] = 72;
        }

        $now = new \Bitrix\Main\Type\DateTime();

        // если задача уже выполняется, то дата начало это последнее исполнение
        if($task['UF_STATUS']>0){
            $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

            $db_array = $TaskManager->FulfiCache($task);

            $find_last_queue = [];
            if ($db_array) $find_last_queue = $db_array[0];

            if ($find_last_queue) {
                /*
                if ($find_last_queue['UF_DATE_COMPLETION']){
                    if ($find_last_queue['UF_DATE_COMPLETION']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_DATE_COMPLETION'];
                }else {
                    if ($find_last_queue['UF_PLANNE_DATE']->getTimestamp() > $now->getTimestamp())
                        $now = $find_last_queue['UF_PLANNE_DATE'];
                }
                */
                if ($find_last_queue['UF_PLANNE_DATE']->getTimestamp() > $now->getTimestamp())
                    $now = $find_last_queue['UF_PLANNE_DATE'];

                // TODO Для чего используем прибавку мин. интервала
                if ($PRODUCT['MINIMUM_INTERVAL']['VALUE'])
                    $now->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
            }else
                // минимальный интервал исполнения
                $now->add($PRODUCT['MINIMUM_INTERVAL']['VALUE'] . " hours");
        }else {
            // задержка исполнения
            $now->add($PRODUCT['DELAY_EXECUTION']['VALUE'] . " hours");
        }

        return $now->getTimestamp();
    }

    public function theorDateEnd(array $task){
        $dateEnd = $this->subtype->theorDateEnd($task);

        $PRODUCT = $this->getProductByTask($task);

        $dateTimestamp = $this->dateStartOne($task);

        // ВЫсчитываем сколько займет задача в часах КОЛИЧЕСТВО * МИН ИНТЕРВАЛ МЕЖДУ ИСПОЛНЕНИЯМИ
        $hours = ($task['UF_NUMBER_STARTS']-1) * $PRODUCT['MINIMUM_INTERVAL']['VALUE'];

        //if ($task['ID'] == 130)
        //    throw new SystemException(print_r($task['UF_NUMBER_STARTS'],true));

        // Если задача начата, то вычитаем MINIMUM_INTERVAL
        if($task['UF_STATUS']>0) $hours = $hours - $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
        $DATE_COMPLETION = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateTimestamp)->add($hours." hours")->getTimestamp();

        //throw new SystemException(print_r($task['UF_DATE_COMPLETION'],true));

        return $DATE_COMPLETION;
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        $PRODUCT = $TaskManager->getProductByTask($task);

        $dateStar = $this->dateStartTask($task);
        $now = \PHelp::dateNow($dateStar);

        $task['UF_NUMBER_STARTS'] = $task['UF_NUMBER_STARTS'] - 1;
        $dateList = [];
        $dateList[] = \PHelp::BitrixdateNow($dateStar);
        if ($task['UF_NUMBER_STARTS'] > 0) {
            $diffDays = $now->diff(\DateTime::createFromFormat('U', $task['UF_DATE_COMPLETION']))->format('%a');
            // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения

            $diffhours = $diffDays * 24;

            $step = floor($diffhours / $task['UF_NUMBER_STARTS']);
            if ($PRODUCT['MINIMUM_INTERVAL']['VALUE'] > $step) $step = $PRODUCT['MINIMUM_INTERVAL']['VALUE'];

            //$step = floor($diffDays / $task['UF_NUMBER_STARTS']);


            for ($i = 0; $i < $task['UF_NUMBER_STARTS']; $i++) {
                $calcDaysStep = $step * ($i + 1);
                $now = \PHelp::BitrixdateNow($dateStar);
                //$dateList[$i+1] = $now->add("+" . $calcDaysStep . ' days');
                $dateList[$i+1] = $now->add("+" . $calcDaysStep . ' hours');
            }
        }

        return $dateList;
    }
}