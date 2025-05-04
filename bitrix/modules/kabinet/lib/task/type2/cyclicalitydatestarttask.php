<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;


class Cyclicalitydatestarttask{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function dateStartTask($task){
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

}