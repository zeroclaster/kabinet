<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error;

use Bitrix\Kabinet\messanger\datamanager\LmessangerTable;

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
    }

    public static function onUserLoginExternal(&$result){
        $result = $result->getParameter('result');
        if (empty($result['response']['error'])){

        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $fields = $request->getPostList();

        //$result->getParameter('action')->getName()
    }

    public function newmessageAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();
		$files = $request->getFileList()->toArray();

        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');

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

        return [
            'id'=> $upd_id,
			'datamessage'=>$this->getmessData(),
            'message'=>'Сообщение отправлено!',
            'action' => $post['action']
        ];
	}

    public function showmoreAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        return [
            'datamessage'=>$this->getmessData(),
            'action' => $post['action']
        ];
    }
	
    public function removemessAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
		
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

		return [
		    'datamessage'=>$this->getmessData(),
            'action' => $post['action']
        ];
    }

    public function getmessData(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');

        $f = [];
        foreach ($post as $fieldName=>$value){
            if (preg_match("/^FILTER-(.+)$/is",$fieldName,$matched)){
                $filterFiled = $matched[1];
                $f[$filterFiled] = $value;
            }
        }
        if ($post['UF_QUEUE_ID'] != NULL && $post['UF_QUEUE_ID']>0) {
            $f['UF_QUEUE_ID'] = $post['UF_QUEUE_ID'];
        }elseif ($post['UF_PROJECT_ID'] != NULL && $post['UF_PROJECT_ID']>0){
            $f['UF_PROJECT_ID'] = $post['UF_PROJECT_ID'];
        }
        elseif ($post['UF_TARGET_USER_ID'] != NULL && $post['UF_TARGET_USER_ID']>0){
            $id_list = $post['UF_TARGET_USER_ID'];
            $f = array_merge($f,[
                'LOGIC' => 'AND',
                ['LOGIC' => 'OR','UF_AUTHOR_ID'=>$id_list,'UF_TARGET_USER_ID'=>$id_list]]);
        }
        $messData = $messanger->getData(
            $f,
            $post['OFFSET'],
            $post['LIMIT'],
            $clear = true,
            $new_reset = $post['NEW_RESET']
        );

        return $messData;
    }

    public function getcountAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');

        $f = [];
        foreach ($post as $fieldName=>$value){
            if (preg_match("/^FILTER-(.+)$/is",$fieldName,$matched)){
                $filterFiled = $matched[1];
                $f[$filterFiled] = $value;
            }
        }

        if (!$f) {
            $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
            $user_id = $user['ID'];
            $f = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
            $f = array_merge(['LOGIC' => 'AND', ['LOGIC' => 'OR', 'UF_AUTHOR_ID' => $user_id, 'UF_TARGET_USER_ID' => $user_id]],$f);
        }

        $data = LmessangerTable::getListActive([
            'select'=>[new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')],
            'filter'=>$f
        ])->fetch();


        $returnData = ['count'=> $data['CNT']];
        $returnData['filter'] = $f;
        $returnData['qeury'] = \Bitrix\Main\Entity\Query::getLastQuery();
        $returnData['post'] = print_R($post,true);
        return $returnData;
    }
}
