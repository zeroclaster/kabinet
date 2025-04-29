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
    }

    public static function onUserLoginExternal(&$result){
        $result = $result->getParameter('result');
        if (empty($result['response']['error'])){
        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $fields = $request->getPostList();
    }

    public function editAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $clientManager = $sL->get('Kabinet.Client');

        try {
                $upd_id = $clientManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

		$clientData = $clientManager->getData();

        return [
            'id'=> $upd_id,
			'fields'=>$clientData[0],
            'message'=>'Данные успешно обновлены!'
        ];
	}
}
