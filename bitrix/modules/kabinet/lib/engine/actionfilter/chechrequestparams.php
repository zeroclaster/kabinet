<?php
namespace Bitrix\Kabinet\Engine\ActionFilter;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Engine\ActionFilter\Service;

class Chechrequestparams extends \Bitrix\Main\Engine\ActionFilter\Base
{
    protected const ERROR_CHECK_REQUEST_PARAMS = 'check_request_params';

    final public function __construct()
    {
        parent::__construct();
    }

    final public function onBeforeAction(Event $event)
    {
        return null;


        //$entityValue = (string)Context::getCurrent()->getRequest()->getHeader($this->entityHeaderName);
        //$tokenValue = (string)Context::getCurrent()->getRequest()->getHeader($this->tokenHeaderName);

            //Context::getCurrent()->getResponse()->setStatus(403);
            $this->addError(new Error(
                'Ошибка при передачи параметров!',
                self::ERROR_CHECK_REQUEST_PARAMS
            ));

            return new EventResult(EventResult::ERROR, null, null, $this);


        return null;
    }
}
