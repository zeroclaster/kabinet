<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * Готовится отчет

Фиксация просрочки — через 72 часа.

Администратор может вручную сменить статус на:

8-Отчет на проверке у клиента;
9-Выполнена;

Нет кнопок

 *
 *
 */

class Stage8 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
            return [
                2,   //Пишется текст
                4,   //В работе у специалиста
                6,   //Публикация
                8,   //Отчет на проверке у клиента
                9,   //Выполнено
            ];
        }else{
            return [];
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

        $UF_STATUS = $object->get('UF_STATUS');
        $UF_RESPONSIBLE = $object->get('UF_RESPONSIBLE');

        // Статусы, которые требуют добавления ответственного
        $STATUSES_FOR_RESPONSIBLE_ADDITION = [8,9];

        if (in_array($UF_STATUS, $STATUSES_FOR_RESPONSIBLE_ADDITION)) {
            $updatedResponsible = $this->addResponsibleEntry($UF_RESPONSIBLE, $UF_STATUS);
            $object->set('UF_RESPONSIBLE', $updatedResponsible);
        }
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