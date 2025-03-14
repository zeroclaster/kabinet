<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Briefevents extends \Bitrix\Main\Engine\Controller
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

    public function createAction(){
        $request = $this->getRequest();
        $fields = $request->getPostList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');

        // Берем основные поля объекта
        $f = $projectManager->retrieveOriginalFields($fields);
        // Берем дополнителеные поля объекта
        $f2 = $projectManager->retrieveAdditionalsFields($fields,PROJECTSINFO);
        $f3 = $projectManager->retrieveAdditionalsFields($fields,PROJECTSDETAILS);
        $f4 = $projectManager->retrieveAdditionalsFields($fields,TARGETAUDIENCE);

        try {
            $res = $projectManager->add(array_merge($f, $f2, $f3, $f4));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        return [
            'id'=> $res,
            'message'=>'Проект успешно создан!'
        ];
    }


    public function editAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $request = array_merge($post,$files);

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ProjectManager = $sL->get('Kabinet.Project');
        $infoManager = $sL->get('Kabinet.infoProject');
        $detailsManager = $sL->get('Kabinet.detailsProject');
        $targetManager = $sL->get('Kabinet.targetProject');

        $crearPOST = $ProjectManager->retrieveAdditionalsFields($request);

        try {
            if (empty($crearPOST['ID']))
                $upd_id = $ProjectManager->add($crearPOST);
            else
                $upd_id = $ProjectManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $data = $ProjectManager->getData();
        $key = array_search($upd_id, array_column($data, 'ID'));
        if ($key === false){
            $this->addError(new Error("Не найден ID ".$upd_id. " в данных!", 1));
            return null;
        }

        $project = $data[$key];
        $project_id = $project['ID'];

        $crearPOST = $infoManager->retrieveAdditionalsFields($request);
        $crearPOST['UF_PROJECT_ID'] = $project_id;
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $infoManager->add($crearPOST);
            else
                $upd_id = $infoManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $crearPOST = $detailsManager->retrieveAdditionalsFields($request);
        $crearPOST['UF_PROJECT_ID'] = $project_id;
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $detailsManager->add($crearPOST);
            else
                $upd_id = $detailsManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $crearPOST = $targetManager->retrieveAdditionalsFields($request);
        $crearPOST['UF_PROJECT_ID'] = $project_id;
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $targetManager->add($crearPOST);
            else
                $upd_id = $targetManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }



        $data = $infoManager->getData($project_id);
        $info = $data[$project_id];


        $data = $detailsManager->getData($project_id);
        $details = $data[$project_id];

        $data = $targetManager->getData($project_id);
        $target = $data[$project_id];


        return [
            'id'=> $project_id,
            'fields'=>$project,
            'fields2'=>$info,
            'fields3'=>$details,
            'fields4'=>$target,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    public function editprojectAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        //AddMessage2Log([$post], "my_module_id");
        ////AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ProjectManager = $sL->get('Kabinet.Project');

        try {
                $upd_id = $ProjectManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error('ProjectManager ' . $exception->getMessage(), 1));
            return null;
        }

        $data = $ProjectManager->getData();
        $key = array_search($upd_id, array_column($data, 'ID'));
        if ($key === false){
            $this->addError(new Error("Не найден ID ".$upd_id. " в данных!", 1));
            return null;
        }

        $project = $data[$key];
        $project_id = $project['ID'];

        return [
            'id'=> $project_id,
            'fields'=>$project,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    public function addproductAction(){
        $request = $this->getRequest();
        $fields = $request->getPostList()->toArray();

        $user = (\KContainer::getInstance())->get('user');
        $BRIEF_HLClass = (\KContainer::getInstance())->get(BRIEF_HL);
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');
        $taskManager = $sL->get('Kabinet.Task');


        if (!$fields['id']) {
            $this->addError(new Error('Could not find id', 1));
            return null;
        }

        if (!$fields['project_id']) {
            $this->addError(new Error('Could not find project id', 1));
            return null;
        }

        /*
        if (!$fields['order_id']) {
            $this->addError(new Error('Could not find order_id', 1));
            return null;
        }
        */

        if (!$fields['count']) {
            $this->addError(new Error('Could not find count', 1));
            return null;
        }

        if ($fields['count'] <= 0) {
            $this->addError(new Error('Field Count must be greater than zero', 1));
            return null;
        }

        if ($fields['order_id']) {
            $project_id = $BRIEF_HLClass::getlist([
                'select' => ['ID'],
                'filter' => [
                    'UF_ORDER_ID' => $fields['order_id'],
                    'UF_AUTHOR_ID' => $user->get('ID')
                ],
                'limit'=>1
            ])->fetch();
        }else{
            $project_id = $BRIEF_HLClass::getlist([
                'select' => ['ID'],
                'filter' => [
                    'ID' => $fields['project_id'],
                    'UF_AUTHOR_ID' => $user->get('ID')
                ],
                'limit'=>1
            ])->fetch();
        }

        if (!$project_id){
            $this->addError(new Error('Заказ не относится более чем к одному проекту', 1));
            return null;
        }

        if ($fields['order_id']) {
            try {
                $projectManager->addproductToOrder($fields['order_id'], $fields['id'], $fields['count']);
            } catch (SystemException $exception) {
                $this->addError(new Error($exception->getMessage(), 1));
                return null;
            }
        }else{
            try {
                $order_id = $projectManager->addproductNewOrder($fields['id'], $fields['count']);
            } catch (SystemException $exception) {
                $this->addError(new Error($exception->getMessage(), 1));
                return null;
            }

            $obResult = $BRIEF_HLClass::update($project_id['ID'],['UF_ORDER_ID' => $order_id]);
            if (!$obResult->isSuccess()){
                $err = $obResult->getErrors();
                $this->addError(new Error($err[0]->getMessage(), 1));
                return null;
            }
            $projectManager->getData(true);
        }

        try {
            $newTaskID = $taskManager->add([
                'UF_PROJECT_ID' => $project_id['ID'],
                'UF_PRODUKT_ID' => $fields['id'],
            ]);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $projectData = $projectManager->getData();
        $orderData = $projectManager->orderData();
        $taskData = $taskManager->getData();

        return [
            'id' => $newTaskID,
            'data'=>$projectData,
            'data2'=>$orderData,
            'datatask'=>$taskData,
            'message'=>'Данные успешно обновлены!'
        ];
    }

    public function removeproductAction(){
        $request = $this->getRequest();
        $fields = $request->getPostList()->toArray();

        if (!$fields['id']) {
            $this->addError(new Error('Could not find id', 1));
            return null;
        }

        if (!$fields['order_id']) {
            $this->addError(new Error('Could not find order_id', 1));
            return null;
        }

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');
        $taskManager = $sL->get('Kabinet.Task');

        try {
            $projectManager->removeproductToOrder($fields['order_id'], $fields['id']);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $projectData = $projectManager->getData();
        $orderData = $projectManager->orderData();
        $taskData = $taskManager->getData();

        return [
            'data'=>$projectData,
            'data2'=>$orderData,
            'datatask'=>$taskData,
            'message'=>'Данные успешно обновлены!'
        ];
    }

	public function edittaskAction(){
		$request = $this->getRequest();
        $fields = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();
		
		AddMessage2Log([$fields], "my_module_id");
        AddMessage2Log($files, "my_module_id");
		AddMessage2Log([$_FILES], "my_module_id");
		
	}
}
