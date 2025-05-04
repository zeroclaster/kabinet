<?php
namespace Bitrix\Kabinet\task\type2;

use \Bitrix\Main\SystemException;

class Createf{
    private $parent;

    function setConteiner($parent){
        $this->parent = $parent;
    }

    public function createFulfi($task,$PlannedDate){
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');
        $TaskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');
        $PRODUCT = $TaskManager->getProductByTask($task);

        //Элемент тип
        $type = $PRODUCT['ELEMENT_TYPE']['VALUE'];
        $CYCLICALITY = $task['UF_CYCLICALITY'];

        $taskObject = $TaskManager->getItem($task);
        $FINALE_PRICE = $taskObject->calcPlannedFinalePrice($task,$PlannedDate);
        $onePrice = $FINALE_PRICE / $task['UF_NUMBER_STARTS'];


        for ($i = 0; $i < count($PlannedDate); $i++) {
            $obResult = $HLBClass::add([
                'UF_TASK_ID' => $task['ID'],
                'UF_ELEMENT_TYPE' => $type,
                'UF_CREATE_DATE' => new \Bitrix\Main\Type\DateTime(),
                'UF_PLANNE_DATE' => $PlannedDate[$i],
                'UF_MONEY_RESERVE' => $onePrice,
                'UF_NUMBER_STARTS' => $task['UF_NUMBER_STARTS'],
                //'UF_DATE_COMPLETION' => \Bitrix\Main\Type\DateTime::createFromTimestamp($task['UF_DATE_COMPLETION']),
            ]);
            if (!$obResult->isSuccess()) {
                $err = $obResult->getErrors();
                throw new SystemException("Ошибка при создании планирования. " . $err[0]->getMessage());
            }

            $ID = $obResult->getID();
        }
    }
}