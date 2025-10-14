<?

use Bitrix\Kabinet\exceptions\BillingException;
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\SystemException,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;
use Bitrix\telegram\Billingnotificationhandler;
use Bitrix\telegram\exceptions\TelegramException;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class BalanceOperationsComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    const ERROR_TEXT = 1;
    const ERROR_404 = 2;

    protected $errorCollection;

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }

    public function onPrepareComponentParams($params)
    {
        if (empty($params['COUNT'])) $params['COUNT'] = 10;

        // Обрабатываем фильтр как в вашем примере
        $arrFilter = [];
        if (!empty($params["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["FILTER_NAME"]))
        {
            $arrFilter = $GLOBALS[$params["FILTER_NAME"]] ?? [];
            if (!is_array($arrFilter))
            {
                $arrFilter = [];
            }
        }
        $params["FILTER"] = $arrFilter;

        return $params;
    }

    public function executeComponent()
    {
        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $this->prepareData();
        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function prepareData()
    {
        $this->arResult["CLIENT_DATA"] = [];
        $this->arResult["BILLING_DATA"] = [];
        $this->arResult["TOTAL"] = 0;
        $this->arResult['SEARCH_RESULT'] = [];

        // Обрабатываем поисковые параметры
        $post = $this->request->getPostList()->toArray();
        $SEARCH_RESULT = &$this->arResult['SEARCH_RESULT'];

        if($post['clientidsearch']){
            $SEARCH_RESULT['clientidsearch'] = $post['clientidsearch'];
        }
        if($post['clienttextsearch'] && !$post['clientidsearch']) {
            $SEARCH_RESULT['clienttextsearch'] = $post['clienttextsearch'];
        }

        // Получаем менеджер клиентов
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ClientManager = $sL->get('Kabinet.Client');
        $billing = $sL->get('Kabinet.Billing');

        // ЕСЛИ ВЫБРАН КОНКРЕТНЫЙ КЛИЕНТ - ЗАГРУЖАЕМ ТОЛЬКО ЕГО ДАННЫЕ
        if (!empty($SEARCH_RESULT['clientidsearch']) && $SEARCH_RESULT['clientidsearch'] > 0) {
            $clientId = (int)$SEARCH_RESULT['clientidsearch'];

            // Загружаем данные только выбранного клиента
            $this->arResult["CLIENT_DATA"] = $ClientManager->getData([], ['ID' => [$clientId]]);
            $this->arResult["TOTAL"] = count($this->arResult["CLIENT_DATA"]);

            // Получаем данные биллинга для выбранного клиента
            if (!empty($this->arResult["CLIENT_DATA"])) {
                $client = $this->arResult["CLIENT_DATA"][0];
                $billingData = $billing->getData(false, ['UF_AUTHOR_ID' => $client['ID']]);
                $this->arResult["BILLING_DATA"] = $billingData ?: [];
            }
        } else {
            // ЕСЛИ КЛИЕНТ НЕ ВЫБРАН - dataclient БУДЕТ ПУСТЫМ МАССИВОМ
            $this->arResult["CLIENT_DATA"] = [];
            $this->arResult["BILLING_DATA"] = [];
            $this->arResult["TOTAL"] = 0;
        }
    }

    public function searchClientsAction()
    {
        $post = $this->request->getPostList()->toArray();
        $searchText = trim($post['search']);

        $output = [];

        $filter = [
            'ACTIVE' => 1,
            'UF_GROUP_REF.GROUP_ID' => REGISTRATED
        ];

        if (!empty($searchText)) {
            $filter['%NAME'] = $searchText;
        }

        $data = \Bitrix\Kabinet\UserTable::getlist([
            'select'=>['ID','LOGIN','NAME','LAST_NAME','SECOND_NAME','EMAIL'],
            'filter'=>[
                'ACTIVE'=>1,
                'UF_GROUP_REF.GROUP_ID'=>REGISTRATED,
                '>PROJECTS.ID'=>0,
                'PROJECTS.UF_ACTIVE'=>1
            ],
            'order'=>['NAME'=>'ASC','EMAIL'=>'ASC'],
            'group'=>['ID'],
        ])->fetchAll();

        foreach ($data as $item){
            $userName = current(array_filter([
                trim(implode(" ", [$item['LAST_NAME'], $item['NAME'], $item['SECOND_NAME']])),
                $item['LOGIN']
            ]));

            $output[] = [
                "value" => $userName . ' ' . $item['EMAIL'] . ' (ID' . $item['ID'] . ')',
                'id' => $item['ID'],
                'name' => $userName,
                'email' => $item['EMAIL']
            ];
        }

        return [
            'success' => true,
            'clients' => $output
        ];
    }

    public function bankTransferAction()
    {
        $warning = '';
        $post = $this->request->getPostList()->toArray();

        $clientId = (int)$post['client_id'];
        $amount = (float)$post['amount'];

        if (!$clientId || $amount <= 0) {
            $this->errorCollection[] = new Error('Неверные данные для пополнения!', 1);
            return null;
        }

        try {
            $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');

            $commission = $amount * 0.03;
            $finalAmount = $amount * 0.97;

            $operation_id = $billing->addMoney($amount, $clientId, $this,"Пополнение баланса. Банковский перевод. ");
            $calc_sum =  round($commission,2);
            $billing->getMoney($calc_sum, 0, $billing, 'Комиссионный сбор');

            // Создаем обработчик для уведомлений биллинга
            $billingHandler = new Billingnotificationhandler();
            try {
                $billingHandler->handleBillingOperation($operation_id);

                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Уведомление биллинга отправлено пользователю {$clientId}: операция ID {$operation_id}" . "\n",
                    FILE_APPEND
                );
            } catch (\Exception $e) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Ошибка отправки уведомления биллинга для операции {$operation_id}: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }

            try {
                $bot = new \Bitrix\telegram\Telegrambothandler();
                $bot->sendMessageToUserTelegram($clientId, "Пополнение баланса. Банковский перевод. {$amount} руб.");
            } catch (\Exception $e) {
                $warning = 'Не удалось отправить Telegram-уведомление';
            }

            $response = [
                'success' => true,
                'message' => 'Баланс успешно пополнен',
                'data' => []
            ];

            if ($warning) {
                $response['warning'] = $warning;
            }

            return $response;

        } catch (\Exception $e) {
            $this->errorCollection[] = new Error($e->getMessage(), 1);
            return null;
        }
        catch (TelegramException $e) {
            $this->errorCollection[] = new Error("Telegram send failed to user {$clientId}: " . $e->getMessage(), 1);
            return null;
        }
    }

    public function freeReplenishmentAction()
    {
        $warning = '';
        $post = $this->request->getPostList()->toArray();

        $clientId = (int)$post['client_id'];
        $amount = (float)$post['amount'];
        $comment = trim($post['comment']);

        if (!$clientId || $amount <= 0) {
            $this->errorCollection[] = new Error('Неверные данные для пополнения!', 1);
            return null;
        }

        try {
            $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
            $operation_id = $billing->addMoney($amount, $clientId, $this,"Пополнение баланса. ".$comment);

            // Создаем обработчик для уведомлений биллинга
            $billingHandler = new Billingnotificationhandler();
            try {
                $billingHandler->handleBillingOperation($operation_id);

                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Уведомление биллинга отправлено пользователю {$clientId}: операция ID {$operation_id}" . "\n",
                    FILE_APPEND
                );
            } catch (\Exception $e) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Ошибка отправки уведомления биллинга для операции {$operation_id}: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }

            try {
                $bot = new \Bitrix\telegram\Telegrambothandler();
                $bot->sendMessageToUserTelegram($clientId, "Пополнение баланса. {$comment} {$amount} руб.");
            } catch (\Exception $e) {
                $warning = 'Не удалось отправить Telegram-уведомление';
            }

            $response = [
                'success' => true,
                'message' => 'Баланс успешно пополнен',
                'data' => []
            ];

            return $response;

        } catch (\Exception $e) {
            $this->errorCollection[] = new Error($e->getMessage(), 1);
            return null;
        }
    }

    public function withdrawAction()
    {
        $warning = '';
        $post = $this->request->getPostList()->toArray();

        $clientId = (int)$post['client_id'];
        $amount = (float)$post['amount'];
        $comment = trim($post['comment']);

        if (!$clientId || $amount <= 0) {
            $this->errorCollection[] = new Error('Неверные данные для списания!', 1);
            return null;
        }

        try {
            $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
            $operation_id = $billing->getMoney($amount, 0, $billing, "Списание с баланса. {$comment} {$amount} руб.");

            // Создаем обработчик для уведомлений биллинга
            $billingHandler = new Billingnotificationhandler();
            try {
                $billingHandler->handleBillingOperation($operation_id);

                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Уведомление биллинга отправлено пользователю {$clientId}: операция ID {$operation_id}" . "\n",
                    FILE_APPEND
                );
            } catch (\Exception $e) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
                    date('[Y-m-d H:i:s] ') . "Ошибка отправки уведомления биллинга для операции {$operation_id}: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }

            try {
                $bot = new \Bitrix\telegram\Telegrambothandler();
                $bot->sendMessageToUserTelegram($clientId, "Списание с баланса. {$comment} {$amount} руб.");
            } catch (\Exception $e) {
                $warning = 'Не удалось отправить Telegram-уведомление';
            }

            $response = [
                'success' => true,
                'message' => 'Списание выполнено успешно',
                'data' => []
            ];

            return $response;

        } catch (\Exception $e) {
            $this->errorCollection[] = new Error($e->getMessage(), 1);
            return null;
        }
    }

    public function getbillingdataAction()
    {
        $post = $this->request->getPostList()->toArray();
        $clientId = (int)$post['client_id'];

        if (!$clientId) {
            $this->errorCollection[] = new Error('Не указан ID клиента', 1);
            return null;
        }

        try {
            $billing = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing');
            $billingData = $billing->getData(false, ['UF_AUTHOR_ID' => $clientId]);

            return [
                'success' => true,
                'billingData' => $billingData ?: []
            ];
        } catch (\Exception $e) {
            $this->errorCollection[] = new Error($e->getMessage(), 1);
            return null;
        }
    }

    protected function hasErrors()
    {
        return (bool)count($this->errorCollection);
    }

    protected function processErrors()
    {
        if (!empty($this->errorCollection)) {
            foreach ($this->errorCollection as $error) {
                ShowError($error->getMessage());
            }
        }
        return false;
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function configureActions()
    {
        return [
            'bankTransfer' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'freeReplenishment' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'withdraw' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'searchClients' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'getbillingdata' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ]
        ];
    }

}