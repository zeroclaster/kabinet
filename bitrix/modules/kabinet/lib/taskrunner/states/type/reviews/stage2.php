<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

use \Bitrix\Kabinet\DateTime;
//use \Bitrix\Main\Type\DateTime;

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

    // когда пришли на статус
    public function cameTo($object){
        $UF_TASK_ID = $object->get('UF_TASK_ID');
        $HLBClass = (\KContainer::getInstance())->get('TASK_HL');

        $siteuser = (\KContainer::getInstance())->get('siteuser');

        $obResult = $HLBClass::update($UF_TASK_ID,['UF_MANAGER_ID'=>$siteuser->get('ID')]);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }
    }

    public function execute(){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');
        $runnerFields = $this->runnerFields;

        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

        $TASK = $this->getTask();

        //echo "<pre>";
        //print_r($TASK);
        //echo "</pre>";
	
		$PRODUCT = $this->getProduct();
		
		// из услуг, если или нет вообще согласование
		if ($PRODUCT['COORDINATION']['VALUE_XML_ID'] == \Bitrix\Kabinet\task\Taskmanager::IS_SOGLACOVANIE){
					
				//throw new SystemException("Test Stop!");
				
				// нет, материал предоставляет клиент
				if ($TASK['UF_COORDINATION'] == 11){
					$this->goToState(3);		// Ожидается текст от клиента
				}
				
				// 12 нет, материал готовим сервис по брифу и публикует без согласования
				// 13 согласование есть
				if ($TASK['UF_COORDINATION'] == 12 || $TASK['UF_COORDINATION'] == 13){
					$this->goToState(2);		// Пишется текст
				}	
		}else{
			$this->goToState(2);		// Пишется текст
		}
		

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