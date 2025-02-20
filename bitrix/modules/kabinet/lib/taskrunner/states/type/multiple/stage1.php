<?php
namespace Bitrix\Kabinet\taskrunner\states\type\multiple;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

//use \Bitrix\Kabinet\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * 0-Запланирован (но не начат);


Создает объект типа «исполнение» со статусом 0-Запланирован

Резервирование средств на счете клиента.

Сущность ожидает дату автоматического перехода в статус 1-Взят в работу.
Вычисляется дата/время перехода в статус «Взят в работу» = планируемая дата публикации – задержка исполнения.


1-Взят в работу;
10-Отменена;
s

10-Отменена;

 *
 *
 *
 */

class Stage1 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
    protected $title = '';
    public $runnerFields = [];
    public $id = 0;
    public $status = 1;

    public function __construct($runnerFields)
    {
        $this->runnerFields = $runnerFields;
        $this->id = $runnerFields['ID'];
    }

    public function setTitle(string $title){
        $this->title = $title;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getName(){
        return implode('',array_slice(explode('\\',__CLASS__),-2,2));
    }

    public function getRoutes(){
        if(\PHelp::isAdmin()) {
            return [1,10];
        }else{
            return [10];
        }
    }


    public function conditionsTransition($oldData){
        $runnerFields = $this->runnerFields;

        if (\PHelp::isAdmin()) {
            // Для админа
        }else{

        }

        return true;
    }

    public function leaveStage($object){
        $object->set('UF_COMMENT','');
        $object->set('UF_HITCH',0);
    }

    public function execute(){
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$BillingManager = $sL->get('Kabinet.Billing');
        $runnerFields = $this->runnerFields;

        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

		// TODO AKULA перенести в начало выполнения, если будет ошибка в выпонлении то задача сдвинется в конец а не подвиснит в начале списка
        $Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
        $Queue->goToEndLine($this->id);


        $now = new DateTime();


        if ($runnerFields['UF_PLANNE_DATE'] < $now->getTimestamp()) {
            $task = $this->getTask();
            $user_id = $task['UF_AUTHOR_ID'];

            $value = $task['FINALE_PRICE'];

            //throw new SystemException("STOP TEST");
            if ($BillingManager->teorygetMoney($value, $user_id)) {

                /*
                $HLBClass = (\KContainer::getInstance())->get(FULF_HL);
                $obResult = $HLBClass::update($runnerFields['ID'], ['UF_MONEY_RESERVE' => $value]);
                if (!$obResult->isSuccess()) {
                    $err = $obResult->getErrors();
                    $mess = $err[0]->getMessage();
                    throw new SystemException($mess);
                }
                $this->runnerFields['UF_MONEY_RESERVE'] = $value;
                */
                $this->goToState(1);
            }
        }

    }

    public function getStatus(){
        return $this->status;
    }

    public function getId(){
        return $this->id;
    }

}