<?php
namespace Bitrix\Kabinet\taskrunner\states;

use Bitrix\Kabinet\DateTime;

class Basestate{
	
	public $command;
	
	public function runCommand($name){
		if(!$this->command) return false;
		
		$res = $this->command->executeCommand($name);
		
		return $res;
	}

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){

    }

    // уходят со статуса
    public function leaveStage($object){

    }

    // когда пришли на статус
    public function cameTo($object){

    }

    public function getProduct(){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
		
		$runnerFields = $this->runnerFields;
		$UF_TASK_ID = $runnerFields['UF_TASK_ID'];

		
        $TaskData = $TaskManager->getData(true,[],['ID'=>$UF_TASK_ID]);
		$TaskData = $TaskData[0];

        $PRODUCT = $TaskManager->getProductByTask($TaskData);

        return $PRODUCT;
    }
	
	public function getTask(){
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$taskManager = $sL->get('Kabinet.Task');
		$runnerFields = $this->runnerFields;
		
		$UF_TASK_ID = $runnerFields['UF_TASK_ID'];
        $TaskData = $taskManager->getData(true,[],['ID'=>$UF_TASK_ID]);
        $TaskData = $TaskData[0];
			
		return $TaskData;
	}

    public function goToState($id){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        $runnerFields = $this->runnerFields;
        \utilCron1::addlog("Автоматический переход на следующую стадию");

        $runnerFields['UF_STATUS'] = $id;
        $upd_id = $RunnerManager->update($runnerFields);
    }

    public function isFixHitch($hour){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        $runnerFields = $this->runnerFields;
        $d = (new DateTime())->add("-".$hour." hours");
        if ($d->getTimestamp() > $runnerFields['UF_CREATE_DATE']){
            $this->runnerFields['UF_HITCH'] = 1;
            $RunnerManager->update($this->runnerFields);
        }
    }
}