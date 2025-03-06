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
        $TaskManager = $sL->get('Kabinet.Task');

        $odlData = $RunnerManager->getData([],false,$post['ID']);
        $odlData = $odlData[0];

        try {
            $upd_id = $RunnerManager->update(array_merge($post,$files));
        }catch (SystemException $exception){

            if ($exception->getCode() == self::END_WITH_SCRIPT){
                $Data = $RunnerManager->getData([],false,$post['ID']);
                foreach($Data as $current){
                    if ($current['ID'] == $post['ID']) break;
                }
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

        $Data = $RunnerManager->getData([],false,$upd_id);
        foreach($Data as $current){
            if ($current['ID'] == $upd_id) break;
        }


        $TaskData = $TaskManager->getData(true,[],['ID'=>$current['UF_TASK_ID']]);
        $TaskData = $TaskData[0];
        if (!$TaskData) {
            $this->addError(new Error("Задачи с ID ".$current['UF_TASK_ID']. ' не найдена!', 1));
            return null;
        }

        $sum = $current['UF_MONEY_RESERVE'] - $odlData['UF_MONEY_RESERVE'];
        $RunnerManager->taskFileds = $TaskData;
        if ($sum>0) {
            $billing->addMoney($sum, $TaskData['UF_AUTHOR_ID'], $RunnerManager);
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

    public function editeAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        // for debug!!
        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        try {
            $upd_id = $RunnerManager->update(array_merge($post,$files));
        }catch (SystemException $exception){

            if ($exception->getCode() == self::END_WITH_SCRIPT){
                $Data = $RunnerManager->getData([],false,$post['ID']);
                foreach($Data as $current){
                    if ($current['ID'] == $post['ID']) break;
                }
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

        $Data = $RunnerManager->getData([],false,$upd_id);
        foreach($Data as $current){
            if ($current['ID'] == $upd_id) break;
        }

        return [
            'id'=> $upd_id,
            'runner'=>$current,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    public function resetAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        // for debug!!
        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        $upd_id = $post['ID'];

        $Data = $RunnerManager->getData([],false,$upd_id);
        foreach($Data as $current){
            if ($current['ID'] == $upd_id) break;
        }

        return [
            'id'=> $upd_id,
            'runner'=>$current,
            'message'=>''
        ];
    }

    public function starttaskAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

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

