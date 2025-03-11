<?php
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Taskevents extends \Bitrix\Main\Engine\Controller
{
    public function __construct(Request $request = null)
    {
        $handler = \Bitrix\Main\EventManager::getInstance()->addEventHandler(
            "main",
            "Bitrix\kabinet\Controller\Briefevents::onAfterAction",
            array(
                "Bitrix\\kabinet\\Controller\\Briefevents",
                "onUserLoginExternal"
            )
        );
        parent::__construct($request);
        $r = $this->getRequest();
        $fields = $r->getPostList();
        //AddMessage2Log(print_r($fields,true), "my_module_id");
    }

    public static function onUserLoginExternal(&$result){
        //AddMessage2Log($result->getParameter('action')->getName(), "my_module_id");
        //AddMessage2Log($result->getParameter('result'), "my_module_id");

        $result = $result->getParameter('result');
        if (empty($result['response']['error'])){

        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $fields = $request->getPostList();
        //AddMessage2Log($fields, "my_module_id");

        //$result->getParameter('action')->getName()

    }

    public function edittaskcopyAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $HLBClass = $TaskManager->getHLBClass();
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');
        $upd_id = $post['ID'];

        try {
            $BDfield = $TaskManager->preparationUpdate(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        foreach ($BDfield as $key => $item)
            if (is_array($item)) $BDfield[$key] = serialize($item);


        $dataSQL = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
            'select'=>['*'],
            'filter'=>['UF_AUTHOR_ID'=>$user_id],
            'order'=>["ID"=>'ASC']
        ])->fetchAll();


        $key = array_search($upd_id, array_column($dataSQL, 'ID'));
        if ($key === false) {
            $this->addError(new Error("Объект с ID ".$upd_id." не найден в базе", 1));
            return null;
        }
        $dataSQL[$key] = array_merge($dataSQL[$key],$BDfield);

        $taskData = $TaskManager->remakeData($dataSQL);

        return [
            'id'=> $upd_id,
            'task'=>$taskData,
        ];
    }

    public function edittaskAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();
		
		//AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        
        try {
            $upd_id = $TaskManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }
		
		/*
		*Берем только один объект задачи, vue не может обновить весь массив
		*/
		$taskData = $TaskManager->getData();
        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));

		
        return [
            'id'=> $upd_id,
			'task'=>$taskData,
            'queue' => $Queue,
            'message'=>'Данные успешно обновлены!'
        ];
	}

    public function starttaskcopyAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $RunnerManager = $sL->get('Kabinet.Runner');
        $projectManager = $sL->get('Kabinet.Project');
        $BillingManager = $sL->get('Kabinet.Billing');


        if (empty($post['ID'])){
            $this->addError(new Error("Нет ID задачи!", 1));
            return null;
        }

        try {
            $upd_id = $TaskManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $task = $TaskManager->getTaskById($post['ID']);
        if (!$task) {
            $this->addError(new Error("Задачи с ID ".$post['ID']. ' не найдена!', 1));
            return null;
        }

        $ClassHLB = (\KContainer::getInstance())->get('TASK_HL');

        try {
            $RunnerManager->startTask($task);
            $ClassHLB::update($task['ID'],['UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED]);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        // исполнения созданы, для Только однократная и запущеной задачи стереть дату завершения
        if ($task['UF_CYCLICALITY'] == 1 && $task['UF_STATUS'] == \Bitrix\Kabinet\task\Taskmanager::WORKED)
            $ClassHLB::update($task['ID'],['UF_DATE_COMPLETION'=>null]);

        $upd_id = $post['ID'];
        $taskData = $TaskManager->getData();
        $orderData = $projectManager->orderData();
        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));
        $dataArray = $BillingManager->getData();

        return [
            'id'=> $upd_id,
            'task'=>$taskData,
            'data2' =>$orderData,
            'queue' => $Queue,
            'billing' => $dataArray,
            'message'=>'Задача успешно запланирована! Ждет выполнения.'
        ];
    }

	public function startAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $RunnerManager = $sL->get('Kabinet.Runner');
        $projectManager = $sL->get('Kabinet.Project');
        $BillingManager = $sL->get('Kabinet.Billing');


		if (empty($post['ID'])){
			$this->addError(new Error("Нет ID задачи!", 1));
            return null;			
		}

        $task = $TaskManager->getTaskById($post['ID']);
		if (!$task) {
            $this->addError(new Error("Задачи с ID ".$post['ID']. ' не найдена!', 1));
            return null;
        }

        $ClassHLB = (\KContainer::getInstance())->get('TASK_HL');


        try {
            $RunnerManager->startTask($task);
            $ClassHLB::update($task['ID'],['UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED]);
		}catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        // исполнения созданы, для Только однократная и запущеной задачи стереть дату завершения
        if ($task['UF_CYCLICALITY'] == 1 && $task['UF_STATUS'] == \Bitrix\Kabinet\task\Taskmanager::WORKED)
            $ClassHLB::update($task['ID'],['UF_DATE_COMPLETION'=>null]);

		$upd_id = $post['ID'];
        $taskData = $TaskManager->getData();
        $orderData = $projectManager->orderData();
        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));
        $dataArray = $BillingManager->getData();

        return [
            'id'=> $upd_id,
			'task'=>$taskData,
            'data2' =>$orderData,
            'queue' => $Queue,
            'billing' => $dataArray,
            'message'=>'Задача успешно запланирована! Ждет выполнения.'
        ];		
	}

    public function stoptaskAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');
        $RunnerManager = $sL->get('Kabinet.Runner');
        $projectManager = $sL->get('Kabinet.Project');

        if (empty($post['ID'])){
            $this->addError(new Error("Нет ID задачи!", 1));
            return null;
        }

        $task = $TaskManager->getTaskById($post['ID']);
        if (!$task) {
            $this->addError(new Error("Задачи с ID ".$post['ID']. ' не найдена!', 1));
            return null;
        }

        $ClassHLB = (\KContainer::getInstance())->get('TASK_HL');
        $ClassHLB::update($task['ID'],['UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::STOPPED]);

        try {
            $RunnerManager->stopTask($task);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }


        $upd_id = $post['ID'];
        $taskData = $TaskManager->getData();
        $orderData = $projectManager->orderData();

        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));

        return [
            'id'=> $upd_id,
            'task'=>$taskData,
            'data2' =>$orderData,
            'queue' => $Queue,
            'message'=>'Задача успешно остановлена'
        ];
    }
	
    public function removetaskAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $HLBClassTask = (\KContainer::getInstance())->get('TASK_HL');
        $HLBClassQueue = (\KContainer::getInstance())->get('FULF_HL');
        $HLBClassProject = (\KContainer::getInstance())->get('BRIEF_HL');
        $ClassHLArchiveFulfi = (\KContainer::getInstance())->get(ARCHIVEFULFI_HL);
        $TaskManager = $sL->get('Kabinet.Task');
        $RunnerManager = $sL->get('Kabinet.Runner');
        $projectManager = $sL->get('Kabinet.Project');
        $projectManager = $sL->get('Kabinet.Project');


        if (empty($post['ID'])){
            $this->addError(new Error("Нет ID задачи!", 1));
            return null;
        }

        $task = $HLBClassTask::getlist(['select'=>['*'],'filter'=>['ID'=>$post['ID']],'limit'=>1])->fetch();
        if (!$task) {
            $this->addError(new Error("Задачи с ID ".$post['ID']. ' не найдена!', 1));
            return null;
        }

        $UF_PROJECT_ID = $task['UF_PROJECT_ID'];
        $UF_PRODUKT_ID = $task['UF_PRODUKT_ID'];

        $project = $HLBClassProject::getlist(['select'=>['*'],'filter'=>['ID'=>$UF_PROJECT_ID],'limit'=>1])->fetch();
        if (!$project) {
            $this->addError(new Error("Проект с ID ".$UF_PROJECT_ID. ' не найдена!', 1));
            return null;
        }
        $UF_ORDER_ID = $project['UF_ORDER_ID'];

        try {
			$task_id_archive = $TaskManager->toArchive($task);
			$runnerArchive = $RunnerManager->toArchive($task);
			foreach ($runnerArchive as $item) $ClassHLArchiveFulfi::update($item,['UF_TASK_ID'=>$task_id_archive]);

			// Удаляем задачу -> исполения задачи -> сообщения
            $TaskManager->delete($task['ID']);

            // Удаляем из проекта заказ
            $orderList = $projectManager->orderData();
            $order = $orderList[$UF_ORDER_ID][$UF_PRODUKT_ID];
            $BASKET_ID = $order['BASKET_ID'];
            $projectManager->removeproductToOrder($UF_ORDER_ID,$BASKET_ID);


        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }


        $upd_id = $post['ID'];
        $taskData = $TaskManager->getData();
        $orderData = $projectManager->orderData();

        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));

        return [
            'id'=> $upd_id,
            'task'=>$taskData,
            'data2'=>$orderData,
            'queue' => $Queue,
            'message'=>'Задача успешно отправлена в архив'
        ];
    }	
}
