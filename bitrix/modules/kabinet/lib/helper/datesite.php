<?php
namespace Bitrix\Kabinet\helper;

class Datesite{

    static function getDayHourMinut($sec, $format=''){

        $minutes=floor($sec/60);
        $sec%=60;

        $hours=floor($minutes/60);
        $minutes%=60;

        $day=floor($hours/24);
        $hours%=24;

        $result = array('day'=>$day, 'hours'=>$hours, 'minutes'=>$minutes, 'sec'=>$sec);

        if (empty($format))
            return $result;
        else	{


            if ($result['day']==0 && $result['hours'] == 0 && $result['minutes'] == 0)
                return $result['sec'] . ' секунд';

            if ($result['day']==0 && $result['hours'] == 0){
                if ($result['sec'] == 0)
                    return $result['minutes'].' минут ';

                return $result['minutes'].' минут '.$result['sec'] . ' секунд';
            }
            if ($result['day']==0){
                if ($result['minutes']==0 && $result['sec']==0)
                    return $result['hours'].' часов ';

                return $result['hours'].' часов '.$result['minutes'].' минут '.$result['sec'] . ' секунд';
            }

            return $result['day'].' дней '.$result['hours'].' часов '.$result['minutes'].' минут '.$result['sec'] . ' секунд';
        }
    }

    static function BitrixdateNow($timestamp = 0){
        if ($timestamp)
            $now = \Bitrix\Main\Type\DateTime::createFromTimestamp($timestamp);
        else
            $now = new \Bitrix\Main\Type\DateTime();
        $nowString = $now->format("d.m.Y")." 00:00:01";
        return new \Bitrix\Main\Type\DateTime($nowString,'d.m.Y h:i:s');
    }

    static function dateNow($timestamp = 0){
        $now = new \DateTime();
        if ($timestamp) $now->setTimestamp($timestamp);
        $nowString = $now->format("d.m.Y")." 00:00:01";
        return \DateTime::createFromFormat('d.m.Y h:i:s', $nowString);
    }

    static function compareDates($date1, $date2){
        $date1_string = $date1->format("d.m.Y")." 00:00:01";
        $date2_string = $date2->format("d.m.Y")." 00:00:01";
        $date1_stamp = (new \Bitrix\Main\Type\DateTime($date1_string,'d.m.Y h:i:s'))->getTimestamp();
        $date2_stamp = (new \Bitrix\Main\Type\DateTime($date2_string,'d.m.Y h:i:s'))->getTimestamp();
        return $date1_stamp < $date2_stamp;
    }

    static function timeConvert($value,$to){
        $defaultType = ['years', 'months', 'days', 'weeks', 'hours', 'minutes', 'seconds'];
        $ret = 0;

        if (in_array($to, $defaultType)) {
            switch($to){
                case 'days':
                    $ret = round($value / 24);
                    break;
                case 'hours':
                    $ret = $value;
                    break;
            }

            return $ret;
        }
        return 0;
    }

    static function dimensiontimeConvert($value){
        $interval_ = self::timeConvert($value,'days');

        $ret = $interval_.' дня.';
        if ($interval_ < 1) {
            $interval_ = self::timeConvert($value,'hours');
            $ret = $interval_.' часов.';
        }

        return $ret;
    }

    static function concreteMonth($date){

        // Начало следующего месяца
        $d = (
            new \DateTime($date->format("Y-m-d"))
        )->modify( 'first day of this month' );

        $Start = new \Bitrix\Main\Type\DateTime($d->format("d.m.Y 00:00:01"), "d.m.Y 00:00:01");

        // Конец следующего месяца
        $d = (
            new \DateTime($date->format("Y-m-d") )
        )->modify( 'last day of this month' );
        // + 23 часа 59 мин. 0 сек.
        $d = $d->getTimestamp() + 86340;
        $End = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

        return [$Start,$End];
    }

    static function concretenextMonth($date){

        // Начало следующего месяца
        $d = (
            new \DateTime($date->format("Y-m-d") )
        )->modify( 'first day of next month' );
        $Start = new \Bitrix\Main\Type\DateTime($d->format("d.m.Y 00:00:01"), "d.m.Y 00:00:01");

        // Конец следующего месяца
        $d = (
            new \DateTime($date->format("Y-m-d") )
        )->modify( 'last day of next month' );
        // + 23 часа 59 мин. 0 сек.
        $d = $d->getTimestamp() + 86340;
        $End = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

        return [$Start,$End];
    }

    static function nextMonth(){
        // Начало следующего месяца
        $Start = new \Bitrix\Main\Type\DateTime(
            (new \DateTime('first day of next month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        );

        $d = new \DateTime('last day of next month');
        // + 23 часа 59 мин. 0 сек.
        $d = $d->getTimestamp() + 86340;

        // Конец следующего месяца
        $End = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

        return [$Start,$End];
    }

    static function actualMonth(){
        // Начало месяца
        $Start = new \Bitrix\Main\Type\DateTime(
            (new \DateTime('first day of this month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        );

        $d = new \DateTime('last day of this month');
        // + 23 часа 59 мин. 0 сек.
        $d = $d->getTimestamp() + 86340;

        // Конец следующего месяца
        $End = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

        /*
        // Конец месяца
        $End = (new \Bitrix\Main\Type\DateTime(
            (new \DateTime('last day of this month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        ));
        */

        return [$Start,$End];
    }

    static function lastMonth(){
        // Начало месяца
        $Start = new \Bitrix\Main\Type\DateTime(
            (new \DateTime('first day of last month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        );

        /*
        // Конец месяца
        $End = (new \Bitrix\Main\Type\DateTime(
            (new \DateTime('last day of last month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        ));
        */

        $d = new \DateTime('last day of last month');
        // + 23 часа 59 мин. 0 сек.
        $d = $d->getTimestamp() + 86340;

        // Конец следующего месяца
        $End = \Bitrix\Main\Type\DateTime::createFromTimestamp($d);

        return [$Start,$End];
    }

    static function monthName($n){
        $months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );
        return $months[$n];
    }

}