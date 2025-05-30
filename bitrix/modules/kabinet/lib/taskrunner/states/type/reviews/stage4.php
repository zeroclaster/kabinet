<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;


/*
 3-Ожидается текст от клиента.

Фиксация просрочки — через 72 часа.
Администратор может вручную сменить статус на:
4-В работе у специалиста;
6-Публикация;
10-Отменена;

Клиент может перевести на стадию
4-В работе у специалиста;
 *
 *
 *
 */

class Stage4 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
            return [6];
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
        $object->set('UF_COMMENT','');
        $object->set('UF_HITCH',0);
    }

    // когда пришли на статус
    public function cameTo($object){
        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');

        $QUEUE_ID=$object->get('ID');
        $TASK_ID=$object->get('UF_TASK_ID');
        $upd_id = $messanger->sendSystemMessage(
            $messanger->config('ispolnitel_ozhidaet_tekst'),
            $QUEUE_ID,
            $TASK_ID
        );
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

        //Фиксация просрочки — через 72 часа.
        $this->isFixHitch(72);

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