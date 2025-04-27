<?
namespace Bitrix\Kabinet\task\factory;

use \Bitrix\Kabinet\task\type\Todate_1,
    \Bitrix\Kabinet\task\type\Todate_33,
    \Bitrix\Kabinet\task\type\Todate,
    \Bitrix\Kabinet\task\type\Cyclicality_2,
    \Bitrix\Kabinet\task\type\Cyclicality_34,
    \Bitrix\Kabinet\task\type\Cyclicality,
    \Bitrix\Kabinet\task\type\Multipleitem,
    \Bitrix\Kabinet\task\type\Base;


class Itemfactory extends Abstractfactory{

    public function getObject($task){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $PRODUCT = $TaskManager->getProductByTask($task);

        //Элемент тип
        $type = $PRODUCT['ELEMENT_TYPE']['VALUE'];
        $CYCLICALITY = $task['UF_CYCLICALITY'];

        if ($CYCLICALITY == 1)
            $item = new Todate_1(
                new Todate(new Base($task)));

        if ($CYCLICALITY == 33)
            $item = new Todate_33(
                new Todate(new Base($task)));

        if ($CYCLICALITY == 2)
            $item = new Cyclicality_2(
                new Cyclicality(new Base($task)));

        if ($CYCLICALITY == 34)
            $item = new Cyclicality_34(
                new Cyclicality(new Base($task)));

        if ($type == 'multiple') $item = new Multipleitem($item);

        return $item;
    }

}