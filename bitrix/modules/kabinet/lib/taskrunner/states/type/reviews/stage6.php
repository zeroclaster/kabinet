<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;
	
use \Bitrix\Kabinet\taskrunner\states\Commandmanager;	

use \Bitrix\Kabinet\DateTime;
//use \Bitrix\Main\Type\DateTime;


/*
 *5-На согласовании (у клиента);

 *
 * Фиксация просрочки — через 72 часа.
Клиент может перевести на стадию
4-В работе у специалиста;
6-Публикация;

Администратор может вручную сменить статус на:
2-Пишется текст;
4-В работе у специалиста;
6-Публикация;


Согласовано, опубликовать ► Публикация
Отклонить с комментарием ► В работе у специалиста


 *
 *
 */

class Stage6 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
    protected $title = '';
    public $runnerFields = [];
    public $id = 0;
    public $status = 1;

    public function __construct($runnerFields)
    {
        $this->runnerFields = $runnerFields;
        $this->id = $runnerFields['ID'];
		
		$this->command = new Commandmanager($this);
		$this->command->setCommand("auto_walk", new \Bitrix\Kabinet\taskrunner\states\commands\Autowalk(6));
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
            return [4,6];
        }
    }

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){
        $runnerFields = $this->runnerFields;

        if (\PHelp::isAdmin()) {
            if (!$runnerFields['UF_REVIEW_TEXT']) throw new SystemException("Вы не ввели текст отзыва");
        }else{

        }

        return true;
    }

    // уходят со статуса
    public function leaveStage($object){
        $object->set('UF_HITCH',0);
    }

    // когда пришли на статус
    public function cameTo($object){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');

        $QUEUE_ID=$object->get('ID');
        $TASK_ID=$object->get('UF_TASK_ID');
        $upd_id = $messanger->sendSystemMessage(
            $messanger->config('postupil_text_na_proverku'),
            $QUEUE_ID,
            $TASK_ID
        );
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

        //Фиксация просрочки — через 96 часов.
        $this->isFixHitch(96);
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