<?php
namespace Bitrix\Kabinet\taskrunner\states\type\multiple;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
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
            return [1,2,3,4,5,6,7,8,9,10];
        }else{
            return [10];
        }
    }


    public function conditionsTransition($oldData){
        if (\PHelp::isAdmin()) {
            // Для админа
        }else{
        }
        return true;
    }

    // уходят со статуса
    public function leaveStage($object){
        $object->set('UF_COMMENT','');
        $object->set('UF_HITCH',0);

        if (!\PHelp::isAdmin()) {
            // если отмена, то пропускаем
            if($object['UF_STATUS'] == 10) return;
            throw new SystemException("Изменить данный статус может только администратор.");
        }

        // назначаем данной задачи менеджера
        $UF_TASK_ID = $object->get('UF_TASK_ID');
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');

        $siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');

        $obResult = $HLBClass::update($UF_TASK_ID,['UF_MANAGER_ID'=>$siteuser->get('ID')]);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }
    }

    public function execute(){
		$BillingManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
        $runnerFields = $this->runnerFields;

        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

		// TODO AKULA перенести в начало выполнения, если будет ошибка в выпонлении то задача сдвинется в конец а не подвиснит в начале списка
        $Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
        $Queue->goToEndLine($this->id);


        $now = new DateTime();


        if ($runnerFields['UF_PLANNE_DATE'] < $now) {
            $task = $this->getTask();
            $user_id = $task['UF_AUTHOR_ID'];

            $value = $task['FINALE_PRICE'];

            //throw new SystemException("STOP TEST");
            if ($BillingManager->teorygetMoney($value, $user_id)) {

                /*
                $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get(FULF_HL);
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