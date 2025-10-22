<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Runnerevents extends \Bitrix\Main\Engine\Controller
{
    const END_WITH_SCRIPT = 100;


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

    public function correctmoneyAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');
        $billing = $sL->get('Kabinet.Billing');

        $odlData = $RunnerManager->getIDFulfiData($post['ID']);
        try {
            $upd_id = $RunnerManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            if ($exception->getCode() == self::END_WITH_SCRIPT){
                $current = $RunnerManager->getIDFulfiData($post['ID']);
                return [
                    'id'=> 0,
                    'runner'=>[],
                    'message'=>$exception->getMessage()
                ];
            }elseif($exception->getCode() == 200){

            }
            else{
                $this->addError(new Error($exception->getMessage(), 1));
                return null;
            }
        }

        $current = $RunnerManager->getIDFulfiData($post['ID']);

        $TaskData = \Bitrix\Kabinet\task\datamanager\TaskTable::getListActive([
            'select'=>['*'],
            'filter'=>['ID'=>$current['UF_TASK_ID']],
            'limit'=>1
        ])->fetch();
        if (!$TaskData) {
            $this->addError(new Error("Задачи с ID ".$current['UF_TASK_ID']. ' не найдена!', 1));
            return null;
        }

        $sum = $current['UF_MONEY_RESERVE'] - $odlData['UF_MONEY_RESERVE'];
        $RunnerManager->taskFileds = $TaskData;
        if ($sum>0) {
            // исправление по доработкам от 2025-09-12
            // пополнять типо не нужно, сразу списываем
            //$billing->addMoney($sum, $TaskData['UF_AUTHOR_ID'], $RunnerManager);
            $billing->getMoney($sum, $TaskData['UF_AUTHOR_ID'], $RunnerManager);
        }else{
            $billing->cachback2($sum*-1,$TaskData['UF_AUTHOR_ID'],$RunnerManager);
        }

        //$this->addError(new Error(print_r($sum,true), 1));
       // return null;

        return [
            'id'=> $upd_id,
            'runner'=>$current,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    /*
     * при нажатии на статусы, при заполнении полей
     */
    public function editeAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $RunnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');

        try {
            $upd_id = $RunnerManager->update(array_merge($post,$files));
        }catch (SystemException $exception){

            if ($exception->getCode() == self::END_WITH_SCRIPT){
                $current = $RunnerManager->getIDFulfiData($post['ID']);
                return [
                    'id'=> 0,
                    'runner'=>[],
                    'message'=>$exception->getMessage()
                ];
            }elseif($exception->getCode() == 200){

            }
            else{
                $this->addError(new Error($exception->getMessage(), 1));
                return null;
            }
        }

        $current = $RunnerManager->getIDFulfiData($upd_id);
        return [
            'id'=> $upd_id,
            'runner'=>$current,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    public function resetAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $current = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner')->getIDFulfiData($post['ID']);
        return [
            'id'=> $post['ID'],
            'runner'=>$current,
            'message'=>''
        ];
    }

    public function starttaskAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $TaskManager = $sL->get('Kabinet.Task');

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        if (empty($post['ID'])){
            $this->addError(new Error("Нет ID задачи!", 1));
            return null;
        }

        $task = $TaskManager->getTaskById($post['ID']);
        if (!$task) {
            $this->addError(new Error("Задачи с ID ".$post['ID']. ' не найдена!', 1));
            return null;
        }

        /*
        try {
            $RunnerManager->startTask($task);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }
        */

        $upd_id = $post['ID'];

        /*
        *Берем только один объект задачи, vue не может обновить весь массив
        */
        $taskData = $TaskManager->getData();
        foreach($taskData as $current){
            if ($current['ID'] == $upd_id) break;
        }


        $Queue = $sL->get('Kabinet.Runner')->getData(array_column($taskData, 'ID'));

        return [
            'id'=> $upd_id,
            'task'=>$current,
            'queue' => $Queue,
            'message'=>'Задача успешно запланирована!<br>Ждет выполнения.'
        ];
    }
}

