<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Messengerevents extends \Bitrix\Main\Engine\Controller
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

    public function newmessageAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();
		
		//AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');

        $crearPOST = array_merge($post,$files);
        try {
            if (empty($crearPOST['ID']))
                $upd_id = $messanger->add($crearPOST);
            else
                $upd_id = $messanger->update($crearPOST);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }


		$messData = $messanger->getData(['UF_QUEUE_ID'=>$post['UF_QUEUE_ID']],$post['OFFSET'],$post['LIMIT'],true);

        return [
            'id'=> $upd_id,
			'datamessage'=>$messData,
            'message'=>'Сообщение отправлено!'
        ];
	}

    public function showmoreAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        //AddMessage2Log([$post], "my_module_id");
        //AddMessage2Log($files, "my_module_id");

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');

        $messData = [];

        if ($post['UF_QUEUE_ID'] != NULL && $post['UF_QUEUE_ID']>0)
                $messData = $messanger->getData(
                    ['UF_QUEUE_ID'=>$post['UF_QUEUE_ID']],
                    $post['OFFSET'],
                    $post['LIMIT'],
                    $clear=true,
                    $new_reset=$post['NEW_RESET']
                );
        elseif ($post['UF_PROJECT_ID'] != NULL && $post['UF_PROJECT_ID']>0){
            $f = [];
            $f['UF_PROJECT_ID'] = $post['UF_PROJECT_ID'];
            $messData = $messanger->getData(
                $f,
                $post['OFFSET'],
                $post['LIMIT'],
                $clear=true,
                $new_reset=$post['NEW_RESET']
            );
        }
        elseif ($post['UF_TARGET_USER_ID'] != NULL && $post['UF_TARGET_USER_ID']>0){
            $messData = $messanger->getData(
                ['LOGIC' => 'OR',
                    'UF_AUTHOR_ID'=>$post['UF_TARGET_USER_ID'],'UF_TARGET_USER_ID'=>$post['UF_TARGET_USER_ID']],
                $post['OFFSET'],
                $post['LIMIT'],
                $clear=true,
                $new_reset=$post['NEW_RESET']
            );
        }


        return [
            'datamessage'=>$messData,
        ];
    }
	
    public function removemessAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');
		
		if ($post['ID'] == NULL || $post['ID'] == 0){
			$this->addError(new Error("ID сообщения не найдено", 1));
            return null;
		}

        try {
            $messanger->delete($post['ID']);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $messData = $messanger->getData(['UF_QUEUE_ID'=>$post['UF_QUEUE_ID']],$post['OFFSET'],$post['LIMIT'],true);

        return [
            'datamessage'=>$messData,
        ];
    }	
}
