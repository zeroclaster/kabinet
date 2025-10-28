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
    }

    public static function onUserLoginExternal(&$result){
        $result = $result->getParameter('result');
        if (empty($result['response']['error'])){

        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $fields = $request->getPostList();

        //$result->getParameter('action')->getName()
    }

    public function configureActions()
    {
        return [
            'makepaylink' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ]
            ],
            'getbalance' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST) // Разрешаем оба метода
                    ),
                    new ActionFilter\Csrf(),
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
        $billinkdata = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing')->getData();
        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $user_id = $user->get('ID');

        if (!\PHelp::isAdmin()){
            $this->addError(new Error("Недостаточно прав для совершения операции.", 1));
            return null;
        }

        $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
        $billing->addMoney($post['summapopolneniya'], 0, $billing);
        if ($post['percentpopolneniya']>0) $billing->getMoney($post['sumpopolnenia'], 0, $billing, 'Комиссионный сбор');


        return [
            'billinkdata'=>$billing->getData(),
            'message'=>'Данные успешно обновлены!',
        ];
    }

    public function getbalanceAction() {
        try {
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $billingManager = $sL->get('Kabinet.Billing');
            $data = $billingManager->getData();

            return [
                'balance' => $data
            ];
        } catch (\Exception $e) {
            $this->addError(new Error("Ошибка при получении баланса: " . $e->getMessage(), 1));
            return null;
        }
    }
}
