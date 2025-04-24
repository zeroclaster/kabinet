<?
namespace Bitrix\Kabinet\task\type;


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
        $FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task);

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $dateStar = $this->subtype->dateStartTask($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
        $TaskManager = $sL->get('Kabinet.Task');

        $PRODUCT = $TaskManager->getProductByTask($task);

        //TADO тестовое значение задержки исполнения
        // "Задержка исполнения"
        if (empty($PRODUCT['DELAY_EXECUTION']['VALUE'])) $PRODUCT['DELAY_EXECUTION']['VALUE'] = 72;

        // если задача циклическая новая, есть задержка исполнения
        if ($task['UF_CYCLICALITY'] == 2) $DELAY_EXECUTION = $PRODUCT['DELAY_EXECUTION']['VALUE'];
        else $DELAY_EXECUTION = 0;


        if($task['UF_STATUS']>0) {
            // ищем последнее исполнение
            $find_last_queue = $HLBClass::getlist([
                'select' => ['ID', 'UF_PLANNE_DATE', 'UF_DATE_COMPLETION'],
                'filter' => ['UF_TASK_ID' => $task['ID']],
                'order' => ['UF_PLANNE_DATE' => 'desc'],
                'limit' => 1
            ])->fetch();
        }

        if($task['UF_STATUS']>0 && $find_last_queue['UF_PLANNE_DATE']) {

            [$firstDayNextMonth,$lastDayNextMonth] = \PHelp::concretenextMonth($find_last_queue['UF_PLANNE_DATE']);
            //$calc_date = $firstDayNextMonth->add($DELAY_EXECUTION . " hours")->getTimestamp();
            $calc_date = $firstDayNextMonth->getTimestamp();
        }
        else{
            $now = (new \Bitrix\Main\Type\DateTime)->add($DELAY_EXECUTION." hours");
            [$firstDayNextMonth,$lastDayNextMonth] = \PHelp::concretenextMonth($now);

            if ($now > $firstDayNextMonth)
                $calc_date = $firstDayNextMonth->add($DELAY_EXECUTION . " hours")->getTimestamp();
            else
                $calc_date = $now->getTimestamp();
        }

        return $calc_date;
    }

    public function theorDateEnd(array $task){
        $dateEnd = $this->subtype->theorDateEnd($task);


        $PRODUCT = $this->getProductByTask($task);

        // дата начала
        $dateTimestamp = $this->dateStartTask($task);
        [$mouthStart1,$mouthEnd1] = \PHelp::concreteMonth(\Bitrix\Main\Type\DateTime::createFromTimestamp($dateTimestamp));
        [$mouthStart2,$mouthEnd2] = \PHelp::concretenextMonth(\Bitrix\Main\Type\DateTime::createFromTimestamp($dateTimestamp));

        /*
        if ($task['ID'] == 118) {
            $d = (
            new \DateTime(\Bitrix\Main\Type\DateTime::createFromTimestamp($dateTimestamp)->format("Y-m-d") )
            )->modify( 'last day of this month' );
            $d = $d->getTimestamp() + 86399;

            throw new SystemException(print_r([$task['ID'], $d], true));
        }
        */


        $DATE_COMPLETION = $mouthEnd2->getTimestamp();

        // Если задача еще не начата
        if($task['UF_STATUS']==0) {
            if ($dateTimestamp <= $mouthEnd1->getTimestamp()) $DATE_COMPLETION = $mouthEnd1->getTimestamp();
            else $DATE_COMPLETION = $mouthEnd2->getTimestamp();
        }else{
            $DATE_COMPLETION = $mouthEnd1->getTimestamp();
        }

        return $DATE_COMPLETION;
    }

    public function PlannedPublicationDate($task){
        $dateList = $this->subtype->PlannedPublicationDate($task);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        [$mouthStart1,$mouthEnd1] = \PHelp::actualMonth();
        [$mouthStart2,$mouthEnd2] = \PHelp::nextMonth();

        $PRODUCT = $TaskManager->getProductByTask($task);

        $dateStar = $TaskManager->dateStartCicle($task);

        $dateEnd = $TaskManager->theorDateEnd($task);


        ///throw new SystemException(print_r($dateEnd,true));

        $mouthStart = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateStar);
        $dateList = [];
        $dateList[] = $mouthStart;
        $task['UF_NUMBER_STARTS'] = $task['UF_NUMBER_STARTS'] - 1;
        if ($task['UF_CYCLICALITY'] == 2 && $task['UF_NUMBER_STARTS'] > 0) {

            //Day of the month without leading zeros
            if ((new \Bitrix\Main\Type\DateTime())->format("m") == \Bitrix\Main\Type\DateTime::createFromTimestamp($dateEnd)->format("m"))
                $now = (new \Bitrix\Main\Type\DateTime())->format("d");
            else
                $now = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateStar)->format("d");

            $lastDayMonth = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateEnd)->format("d");

            //if ($task['ID'] == 133)
            //   throw new SystemException(print_r($lastDayMonth,true));


            // Если задача не начата
            if ($task['UF_STATUS']==0){
                $d = $lastDayMonth - $now + 1;
                $h = $d*24;

                // Если задача не в работе
                //if($task['UF_STATUS']==0) $h = $h - $PRODUCT['DELAY_EXECUTION']['VALUE'];

                // +1 что появился интервал до первого исполнения след. месяца.
                $step_ = floor($h / ($task['UF_NUMBER_STARTS']+1));

            }
            // Если задача начата
            else{
                // +1 что появился интервал до первого исполнения след. месяца.
                $step_ = floor($lastDayMonth*24 / ($task['UF_NUMBER_STARTS']+1));
            }

            // if ($task['ID'] == 133)
            //  throw new SystemException(print_r($step_,true));

            if ($PRODUCT['MINIMUM_INTERVAL']['VALUE']){
                $step =  $PRODUCT['MINIMUM_INTERVAL']['VALUE'];
                if ($step_ > $step) $step = $step_;
            }
            else {
                // округленный интервал в днях от сегоднешней до введенной пользователем даты завершения
                //$step = floor(30 / $task['UF_NUMBER_STARTS']);
                $step = $step_;
            }



        }else{
            $step = 1;
        }

        //throw new SystemException(print_r($step,true));

        $mStart =  $dateStar;



        // Если задача еще не начата
        if($task['UF_STATUS']==0) $mStart = \Bitrix\Main\Type\DateTime::createFromTimestamp($dateStar)->getTimestamp();

        if ($task['UF_NUMBER_STARTS'] > 0) {
            for ($i = 0; $i < $task['UF_NUMBER_STARTS']; $i++) {
                $calcDaysStep = $step * ($i + 1);

                // постоянно прибавляем к стартовому значению шаг умноженный на позицию
                $calcDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($mStart)->add("+" . $calcDaysStep . ' hours');
                if ($calcDate->getTimestamp() > $dateEnd) break;
                $dateList[$i + 1] = $calcDate;
            }
        }

        return $dateList;
    }
}