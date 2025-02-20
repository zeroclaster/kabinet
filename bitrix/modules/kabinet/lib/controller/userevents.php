<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Userevents extends \Bitrix\Main\Engine\Controller
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

    public function editAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();
		
		//AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $clientManager = $sL->get('Kabinet.Client');

        try {
                $upd_id = $clientManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

		/*
		*Берем только один объект задачи, vue не может обновить весь массив
		*/
		$clientData = $clientManager->getData();

        return [
            'id'=> $upd_id,
			'fields'=>$clientData[0],
            'message'=>'Данные успешно обновлены!'
        ];
	}
}
