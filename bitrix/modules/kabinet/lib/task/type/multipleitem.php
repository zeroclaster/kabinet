<?
namespace Bitrix\Kabinet\task\type;

use \Bitrix\Main\SystemException;

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
        //$FINALE_PRICE = $this->subtype->calcPlannedFinalePrice($task,$PlannedDate);

        $FINALE_PRICE = $task['FINALE_PRICE'];

        return $FINALE_PRICE;
    }

    public function dateStartTask($task){
        $dateStar = $this->subtype->dateStartTask($task);

        return $dateStar;
    }

    public function theorDateEnd($task){
        $DATE_COMPLETION = $this->subtype->theorDateEnd($task);

        return $DATE_COMPLETION;
    }

    public function PlannedPublicationDate($task){
        $dateList_ = $this->subtype->PlannedPublicationDate($task);

        $dateStar = $this->dateStartTask($task);
        $dateList[] = \PHelp::BitrixdateNow($dateStar);

        return $dateList;
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

        $obResult = $HLBClass::add([
            'UF_TASK_ID' => $task['ID'],
            'UF_ELEMENT_TYPE' => $type,
            'UF_CREATE_DATE' => new \Bitrix\Main\Type\DateTime(),
            'UF_PLANNE_DATE' => \Bitrix\Main\Type\DateTime::createFromTimestamp($task['UF_DATE_COMPLETION']), //$PlannedDate[0],
            'UF_MONEY_RESERVE' => $FINALE_PRICE,
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