<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Contractevents extends \Bitrix\Main\Engine\Controller
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

    public function editcontractAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();
		
		//AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        \CUserOptions::SetOption('kabinet','usertype',$post['contracttype'],false,$user->get('ID'));

        $contractManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Contract');

        $crearPOST = $contractManager->retrieveAdditionalsFields(array_merge($post,$files));
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $contractManager->add($crearPOST);
            else
                $upd_id = $contractManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $bankManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Bankdata');
        $crearPOST = $bankManager->retrieveAdditionalsFields(array_merge($post,$files));
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $bankManager->add($crearPOST);
            else
                $upd_id = $bankManager->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

		/*
		*Берем только один объект задачи, vue не может обновить весь массив
		*/
		$contractData = $contractManager->getData();
        $bankData = $bankManager->getData();

        return [
            'id'=> $upd_id,
			'fields'=>$contractData,
            'fields2'=>$bankData,
            'message'=>'Данные успешно обновлены!'
        ];
	}
}
