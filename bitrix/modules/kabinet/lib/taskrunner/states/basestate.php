<?php
namespace Bitrix\Kabinet\taskrunner\states;

// Для отладки, можно установить свою дату задав константу TESTDATE
use Bitrix\Kabinet\DateTime;
//use \Bitrix\Main\Type\DateTime;

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
        if ($d->getTimestamp() > $runnerFields['UF_CREATE_DATE']->getTimestamp()){
            $this->runnerFields['UF_HITCH'] = 1;

            $convertdata = $RunnerManager->remakeFulfiData([$this->runnerFields]);
            $updateFileds = $convertdata[0];
            
            $RunnerManager->update($updateFileds);
        }
    }

    public function isFixHitch2($hour=0){
        $RunnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');

        $runnerFields = $this->runnerFields;
        $d = (new DateTime())->add("-".$hour." hours");
        if ($d->getTimestamp() > $runnerFields['UF_PLANNE_DATE']->getTimestamp()){
            $this->runnerFields['UF_HITCH'] = 1;

            $convertdata = $RunnerManager->remakeFulfiData([$this->runnerFields]);
            $updateFileds = $convertdata[0];

            $RunnerManager->update($updateFileds);
        }
    }

    /**
     * Добавляет новую запись ответственного с указанным статусом
     *
     * @param string|null $currentResponsibleJson Текущее значение UF_RESPONSIBLE в JSON
     * @param string|int $newStatus Новый статус для добавления
     * @return string JSON-строка с обновленным массивом ответственных
     */
    public function addResponsibleEntry($currentResponsibleJson, $newStatus)
    {
        $responsibleArray = [];

        if ($currentResponsibleJson) {
            $responsibleArray = json_decode($currentResponsibleJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $responsibleArray = [];
            }
        }

        // Если есть существующие данные, создаем новую запись
        if (!empty($responsibleArray)) {
            // Получаем последний элемент
            $lastElement = end($responsibleArray);
            $responsibleArray = array_values($responsibleArray); // сбрасываем указатель

            // Создаем новую запись на основе последней
            $newEntry = $lastElement;
            $newEntry['status'] = (string)$newStatus;
            //$newEntry['date'] = date('c');

            // Добавляем новую запись в массив
            $responsibleArray[] = $newEntry;
        }

        return json_encode($responsibleArray);
    }
}