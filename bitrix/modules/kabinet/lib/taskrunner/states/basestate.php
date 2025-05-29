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
        $TaskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');
		$runnerFields = $this->runnerFields;

        $dataSQL = \Bitrix\Kabinet\task\datamanager\TaskTable::getListActive([
            'select'=>['*'],
            'filter'=>['ID'=>$runnerFields['UF_TASK_ID']],
            'limit'=>1
        ])->fetch();
        $TaskData = $TaskManager->remakeData([$dataSQL]);
        $TaskData = $TaskData[0];
			
		return $TaskData;
	}

    public function goToState($id){
        $RunnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');

        $runnerFields = $this->runnerFields;
        $runnerFields['UF_STATUS'] = $id;

        $convertdata = $RunnerManager->remakeFulfiData([$runnerFields]);
        $updateFileds = $convertdata[0];

        \utilCron1::addlog("Автоматический переход на следующую стадию");
        $upd_id = $RunnerManager->update($updateFileds);
    }

    public function isFixHitch($hour){
        $RunnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');

        $runnerFields = $this->runnerFields;
        $d = (new DateTime())->add("-".$hour." hours");
        if ($d->getTimestamp() > $runnerFields['UF_CREATE_DATE']){
            $this->runnerFields['UF_HITCH'] = 1;

            $convertdata = $RunnerManager->remakeFulfiData([$this->runnerFields]);
            $updateFileds = $convertdata[0];
            
            $RunnerManager->update($updateFileds);
        }
    }
}