<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

use \Bitrix\Kabinet\DateTime;
//use \Bitrix\Main\Type\DateTime;

/*
 * 2-Пишется текст;
 *Фиксация просрочки — через 72 часа.
4-В работе у специалиста;


Если «согласование» = «есть» то есть вариант 5-На согласовании (у клиента);

6-Публикация;
10-Отменена;

 *
 *Кнопок нет.

 */

class Stage3 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
            return [];
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