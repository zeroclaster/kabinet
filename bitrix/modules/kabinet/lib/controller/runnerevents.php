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

    public function savenoteAction()
    {
        $post = $this->request->getPostList()->toArray();
        $fulfillmentId = $post['fulfillment_id'] ?? 0;
        $noteText = $post['note_text'] ?? '';

        if (!$fulfillmentId) {
            return ['success' => false, 'message' => 'Неверные параметры'];
        }

        try {
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $siteuser = $sL->get('siteuser');

            // Ищем существующую заметку
            $existingNote = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentNotesTable::getList([
                'filter' => [
                    'UF_FULFILLMENT_ID' => $fulfillmentId,
                    'UF_ACTIVE' => 1
                ],
                'order' => ['UF_CREATED_DATE' => 'DESC'],
                'limit' => 1
            ])->fetch();

            if ($existingNote) {
                // Обновляем существующую заметку
                $result = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentNotesTable::update($existingNote['ID'], [
                    'UF_NOTE_TEXT' => $noteText,
                    'UF_MODIFIED_DATE' => new \Bitrix\Main\Type\DateTime()
                ]);
            } else {
                // Создаем новую заметку
                $result = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentNotesTable::add([
                    'UF_FULFILLMENT_ID' => $fulfillmentId,
                    'UF_NOTE_TEXT' => $noteText,
                    'UF_CREATED_BY' => $siteuser->get('ID'),
                    'UF_CREATED_DATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_NOTE_TYPE' => 1,
                    'UF_ACTIVE' => 1,
                    'UF_IS_PRIVATE' => 0,
                    'UF_PRIORITY' => 1
                ]);
            }

            if ($result->isSuccess()) {
                return ['success' => true, 'message' => 'Заметка сохранена'];
            } else {
                return ['success' => false, 'message' => 'Ошибка сохранения'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getcurrentnoteAction()
    {
        $post = $this->request->getPostList()->toArray();
        $fulfillmentId = $post['fulfillment_id'] ?? 0;

        if (!$fulfillmentId) {
            return ['note' => ''];
        }

        try {
            $note = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentNotesTable::getList([
                'select' => ['UF_NOTE_TEXT'],
                'filter' => [
                    'UF_FULFILLMENT_ID' => $fulfillmentId,
                    'UF_ACTIVE' => 1
                ],
                'order' => ['UF_CREATED_DATE' => 'DESC'],
                'limit' => 1
            ])->fetch();

            return ['note' => $note['UF_NOTE_TEXT'] ?? ''];

        } catch (\Exception $e) {
            return ['note' => ''];
        }
    }

    public function edittableAction()
    {
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $executionId = (int)($post['ID'] ?? 0);
        if (!$executionId) {
            $this->addError(new Error("Не указан ID исполнения", 1));
            return null;
        }

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $RunnerManager = $sL->get('Kabinet.Runner');

        try {
            // Получаем текущие данные исполнения
            $currentData = $RunnerManager->getIDFulfiData($executionId);

            // Подготавливаем данные для обновления
            $updateData = ['ID' => $executionId];

            // Обрабатываем разные типы полей
            foreach ($post as $field => $value) {
                if ($field === 'ID') continue;

                switch ($field) {
                    case 'UF_PLANNE_DATE':
                    case 'UF_ACTUAL_DATE':
                        // Обработка дат
                        if ($value) {
                               $timestamp = strtotime($value);
                            if ($timestamp === false) throw new \Bitrix\Main\SystemException("Ошибка в формате даты");
                                $updateData[$field] = $timestamp;
                        }
                        break;

                    case 'UF_SITE_SETUP_ACCOUNT':
                    case 'UF_SITE_SETUP_LOGIN':
                    case 'UF_SITE_SETUP_PASS':
                    case 'UF_SITE_SETUP_IP':
                        // Обработка данных аккаунта
                        $siteSetup = [];
                        if (!empty($currentData['UF_SITE_SETUP'])) {
                            try {
                                $siteSetup = json_decode($currentData['UF_SITE_SETUP'], true) ?: [];
                            } catch (\Exception $e) {
                                $siteSetup = [];
                            }
                        }

                        // Обновляем соответствующее поле
                        $fieldMap = [
                            'UF_SITE_SETUP_ACCOUNT' => 'accaunt',
                            'UF_SITE_SETUP_LOGIN' => 'login',
                            'UF_SITE_SETUP_PASS' => 'pass',
                            'UF_SITE_SETUP_IP' => 'ip'
                        ];

                        if (isset($fieldMap[$field])) {
                            $siteSetup[$fieldMap[$field]] = $value;
                            $updateData['UF_SITE_SETUP'] = json_encode($siteSetup);
                        }
                        break;

                    default:
                        // Простые текстовые поля
                        $updateData[$field] = $value;
                        break;
                }
            }

            // for debugg
            //throw new \Bitrix\Main\SystemException(print_R($updateData, true));
            // Выполняем обновление
            $upd_id = $RunnerManager->update($updateData);

            // Получаем обновленные данные
            $updatedData = $RunnerManager->getIDFulfiData($executionId);

            return [
                'success' => true,
                'id' => $upd_id,
                'message' => 'Данные успешно обновлены',
                'updated_data' => $updatedData
            ];

        } catch (\Exception $e) {
            $this->addError(new Error($e->getMessage(), 1));
            return null;
        }
    }
}

