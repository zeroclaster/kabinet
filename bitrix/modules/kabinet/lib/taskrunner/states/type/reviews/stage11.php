<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Kabinet\exceptions\FulfiException;
use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * 10-Отменена;
 *
 * Если в статус перешли со стадий «1-9» и по инициативе клиента, то списание 50% зарезервированных средств клиента.


 *
 *
 */


class Stage11 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
            return [0];
        }else{
            return [0];
        }
    }

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){
        if (\PHelp::isAdmin()) {
            // Для админа
        }else{
        }
        return true;
    }

    // уходят со статуса
    public function leaveStage($object){
        $object->set('UF_HITCH',0);

        $runnerFields = $this->runnerFields;
        //$messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
        $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');

        // если возвращаемся обратно на запланировано
        if($object['UF_STATUS'] === 0){
            $task = $this->getTask();
            $isError = $billing->getMoney($runnerFields['UF_MONEY_RESERVE'],$task['UF_AUTHOR_ID'],$this);
            if ($isError === false) throw new SystemException("Недостаточно средств для смены статуса.");
        }

    }

    // когда пришли на статус
    public function cameTo($object){
        $runnerFields = $this->runnerFields;
        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
        $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
        $task = $this->getTask();
        $billing->cachback($runnerFields['UF_MONEY_RESERVE'],$task['UF_AUTHOR_ID'],$this);

        $object->set('UF_MONEY_RESERVE',0);

        // если статус устанавливает Админ
        if (\PHelp::isAdmin()) {
            $QUEUE_ID = $object->get('ID');
            $TASK_ID = $object->get('UF_TASK_ID');
            // отправить сообщение в чат
            $upd_id = $messanger->sendSystemMessage(
                $messanger->config('zadacha otmenena ispolnitelem'),
                $QUEUE_ID,
                $TASK_ID
            );

        }
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();



        $Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
        $Queue->goToEndLine($this->id);
    }

    public function getStatus(){
        return $this->status;
    }

    public function getId(){
        return $this->id;
    }

}