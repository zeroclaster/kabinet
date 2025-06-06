<?php
namespace Bitrix\Kabinet\taskrunner\states\type\service;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * 1-Взят в работу;
 *
 * Если поле «согласование» = «нет, текст пишет клиент», на стадию – через 0 часов
3-Ожидается текст от клиента.


Если поле «согласование» = «нет, материал готовит сервис» или «согласование» = «есть», то на стадию – через 0 часов.
2-Пишется текст;

 *
 *10-Отменена;


Кнопок нет.
 *
 */

class Stage2 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
                3,  //Ожидается уточнения задания от клиента
                4,  // В работе у специалиста
                8,  //Отчет на проверке у клиента
                9,  //Выполнена
                10  //Отменена
            ];
        }else{
            return [];
        }
    }


    public function conditionsTransition($oldData){
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

    // когда пришли на статус
    public function cameTo($object){
        $QUEUE_ID = $object->get('ID');
        $TASK_ID = $object->get('UF_TASK_ID');

        /*
        // отправить сообщение в чат
        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
        $upd_id = $messanger->sendSystemMessage(
            "Тест системного сообщения!",
            $QUEUE_ID,
            $TASK_ID
        );
        */
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