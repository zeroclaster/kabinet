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

    static function BitrixdateNow($datetime = null){
        $now = new \Bitrix\Main\Type\DateTime();

        if ($datetime) $now = $datetime;
        $nowString = $now->format("d.m.Y")." 00:00:01";
        return new \Bitrix\Main\Type\DateTime($nowString,'d.m.Y H:i:s');
    }

    static function compareDates($date1, $date2){
        $date1_string = $date1->format("d.m.Y")." 00:00:01";
        $date2_string = $date2->format("d.m.Y")." 00:00:01";
        $date1 = (new \Bitrix\Main\Type\DateTime($date1_string,'d.m.Y H:i:s'));
        $date2 = (new \Bitrix\Main\Type\DateTime($date2_string,'d.m.Y H:i:s'));
        return $date1 < $date2;
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

        // клонирование и перенос объекта в класс \Bitrix\Kabinet\DateTime;
        $date = \Bitrix\Kabinet\DateTime::createFromTimestamp($date->getTimestamp());

        return [
            $date->modify( 'first day of this month' )->dayStart(),
            $date->modify( 'last day of this month' )->dayEnd()
        ];
    }

    static function concretenextMonth($date){

        // клонирование и перенос объекта в класс \Bitrix\Kabinet\DateTime;
        $date = \Bitrix\Kabinet\DateTime::createFromTimestamp($date->getTimestamp());

        return [
            $date->modify( 'first day of next month' )->dayStart(),
            $date->modify( 'last day of next month' )->dayEnd()
        ];
    }

    static function nextMonth(){

        $date = new \Bitrix\Kabinet\DateTime;
        return [
            $date->modify( 'first day of next month' )->dayStart(),
            $date->modify( 'last day of next month' )->dayEnd()
        ];
    }

    static function actualMonth(){

        $date = new \Bitrix\Kabinet\DateTime;
        return [
            $date->modify( 'first day of this month' )->dayStart(),
            $date->modify( 'last day of this month' )->dayEnd()
        ];
    }

    static function lastMonth(){

        $date = new \Bitrix\Kabinet\DateTime;
        return [
            $date->modify( 'first day of last month' )->dayStart(),
            $date->modify( 'last day of last month' )->dayEnd()
        ];
    }

    static function monthName($n){
        $months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );
        return $months[$n];
    }

}