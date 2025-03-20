<?php
namespace Bitrix\kabinet\Controller;

use Bitrix\Main\Loader,
    Bitrix\Messanger,
    Bitrix\Main\DI,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error,
    Bitrix\Main\Engine\ActionFilter;

class Bilingevents extends \Bitrix\Main\Engine\Controller
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

    public function configureActions()
    {
        //если действия не нужно конфигурировать, то пишем просто так. И будет конфиг по умолчанию
        return [
            'makepaylink' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    //new \Bitrix\Kabinet\Engine\ActionFilter\Chechrequestparams()
                ]
            ],
        ];
    }

    public function makepaylinkAction(){
		$request = $this->getRequest();
        $post = $request->getPostList()->toArray();

        //$this->addError(new Error(print_r($post,true), 1));
        //return null;

        $IsTest = 1;
        if (empty($post['totalsum'])) {
            $this->addError(new Error("Платеж не может быть совершен. Нет суммы платежа.", 1));
            return null;
        }

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billing = $sL->get('Kabinet.Billing');

        try {
            $inv_id = $billing->createTransaction($post['totalsum']);
        }catch (SystemException $exception){
            $this->addError(new Error($exception->getMessage(), 1));
            return null;
        }

        $link = (new \Bitrix\Kabinet\billing\paysystem\robokassa\Result($inv_id))
            ->generatePayLink($post['totalsum'],'');

        return [
			'link'=>$link,
        ];
	}

    public function depositmoneyAction(){
        $request = $this->getRequest();
        $post = $request->getPostList()->toArray();
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billinkdata = $sL->get('Kabinet.Billing')->getData();
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

        if (!\PHelp::isAdmin()){
            $this->addError(new Error("Недостаточно прав для совершения операции.", 1));
            return null;
        }

        $billing = $sL->get('Kabinet.Billing');
        $billing->addMoney($post['summapopolneniya'], 0, $billing);
        if ($post['percentpopolneniya']>0) $billing->getMoney($post['sumpopolnenia'], 0, $billing, 'Комиссионный сбор');


        return [
            'billinkdata'=>$billing->getData(),
            'message'=>'Данные успешно обновлены!',
        ];
    }
}
